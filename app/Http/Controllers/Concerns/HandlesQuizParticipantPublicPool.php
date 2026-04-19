<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Quiz;
use App\Models\QuizAnonymousPoolReservation;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

trait HandlesQuizParticipantPublicPool
{
    private function quizUsesPublicAnonymousPool(Quiz $quiz): bool
    {
        return (bool) $quiz->is_public_anonymous_pool_mode;
    }

    private function sessionUsesPublicAnonymousPool(Quiz $quiz): bool
    {
        return $this->quizUsesPublicAnonymousPool($quiz)
            && (bool) Session::get('public_anonymous_pool_active', false);
    }

    private function publicAnonymousPoolReservationTtlMinutes(Quiz $quiz): int
    {
        $quizMinutes = max(1, (int) ceil(((int) $quiz->time_limit) / 60));

        return max(30, $quizMinutes + 30);
    }

    private function currentPublicAnonymousPoolReservation(Quiz $quiz): ?QuizAnonymousPoolReservation
    {
        $reservationId = Session::get('public_anonymous_pool_reservation_id');

        if (! $reservationId) {
            return null;
        }

        $reservation = QuizAnonymousPoolReservation::query()
            ->where('id', $reservationId)
            ->where('quiz_id', $quiz->id)
            ->first();

        if (! $reservation) {
            Session::forget([
                'public_anonymous_pool_reservation_id',
                'public_anonymous_pool_slot_code',
                'public_anonymous_pool_active',
            ]);

            return null;
        }

        if ($reservation->expires_at?->isPast()) {
            $reservation->delete();
            Session::forget([
                'public_anonymous_pool_reservation_id',
                'public_anonymous_pool_slot_code',
                'public_anonymous_pool_active',
            ]);

            return null;
        }

        return $reservation;
    }

    private function nextAvailableAnonymousSlotCode(Quiz $quiz, array $reservedCodes = []): ?string
    {
        $usedCodes = array_fill_keys(
            array_merge(
                QuizStudent::query()->where('quiz_id', $quiz->id)->pluck('student_code')->all(),
                $reservedCodes
            ),
            true
        );

        for ($number = 1; $number <= 9999; $number++) {
            $code = str_pad((string) $number, 4, '0', STR_PAD_LEFT);

            if ($code === '0000' || isset($usedCodes[$code])) {
                continue;
            }

            return $code;
        }

        return null;
    }

    private function claimPublicAnonymousPoolReservation(Quiz $quiz, Request $request): ?QuizAnonymousPoolReservation
    {
        $sessionId = $request->session()->getId();
        $expiresAt = now()->addMinutes($this->publicAnonymousPoolReservationTtlMinutes($quiz));

        return DB::transaction(function () use ($quiz, $sessionId, $expiresAt) {
            Quiz::query()->whereKey($quiz->id)->lockForUpdate()->first();

            QuizAnonymousPoolReservation::query()
                ->where('quiz_id', $quiz->id)
                ->where('expires_at', '<=', now())
                ->delete();

            $existing = QuizAnonymousPoolReservation::query()
                ->where('quiz_id', $quiz->id)
                ->where('session_id', $sessionId)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->update([
                    'expires_at' => $expiresAt,
                ]);

                return $existing->fresh();
            }

            $capacity = max(1, (int) ($quiz->anonymous_pool_capacity ?? 0));
            $completedCount = QuizAttempt::query()
                ->where('quiz_id', $quiz->id)
                ->whereNotNull('submitted_at')
                ->whereHas('student', function ($query) {
                    $query->where('is_anonymous', true);
                })
                ->count();

            $activeReservations = QuizAnonymousPoolReservation::query()
                ->where('quiz_id', $quiz->id)
                ->lockForUpdate()
                ->get();

            if (($completedCount + $activeReservations->count()) >= $capacity) {
                return null;
            }

            $slotCode = $this->nextAvailableAnonymousSlotCode(
                $quiz,
                $activeReservations->pluck('slot_code')->all()
            );

            if ($slotCode === null) {
                return null;
            }

            return QuizAnonymousPoolReservation::create([
                'quiz_id' => $quiz->id,
                'session_id' => $sessionId,
                'slot_code' => $slotCode,
                'expires_at' => $expiresAt,
            ]);
        });
    }

    private function syncSessionForPublicAnonymousPool(Quiz $quiz, QuizAnonymousPoolReservation $reservation, bool $resetRuntimeState = false): void
    {
        if ($resetRuntimeState) {
            $this->resetQuizRuntimeState();
        }

        Session::put('quiz_id', $quiz->id);
        Session::put('attempt_id', 'guest');
        Session::put('student_code', $reservation->slot_code);
        Session::put('student_name', __('controllers.anonymous_student_name'));
        Session::put('guest_started_at', now());
        Session::put('public_anonymous_pool_active', true);
        Session::put('public_anonymous_pool_reservation_id', $reservation->id);
        Session::put('public_anonymous_pool_slot_code', $reservation->slot_code);
        $this->ensureQuizRouteToken($quiz);
    }

    private function releasePublicAnonymousPoolReservation(Quiz $quiz): void
    {
        $reservationId = Session::get('public_anonymous_pool_reservation_id');

        if ($reservationId) {
            QuizAnonymousPoolReservation::query()
                ->where('id', $reservationId)
                ->where('quiz_id', $quiz->id)
                ->delete();
        }

        Session::forget([
            'public_anonymous_pool_reservation_id',
            'public_anonymous_pool_slot_code',
            'public_anonymous_pool_active',
        ]);
    }

    private function persistPublicAnonymousPoolSubmission(Quiz $quiz): ?QuizAttempt
    {
        $reservation = $this->currentPublicAnonymousPoolReservation($quiz);

        if (! $reservation) {
            return null;
        }

        $questionOrder = array_values(array_map('intval', Session::get('question_order', [])));
        $guestAnswers = Session::get('guest_answers', []);
        $answerOrderMap = Session::get('answer_order_map', []);
        $skippedQuestions = array_values(array_unique(array_map('intval', Session::get('skipped_questions', []))));
        $currentQuestionIndex = max(0, (int) Session::get('current_question_index', count($questionOrder)));

        return DB::transaction(function () use (
            $quiz,
            $reservation,
            $questionOrder,
            $guestAnswers,
            $answerOrderMap,
            $skippedQuestions,
            $currentQuestionIndex
        ) {
            $student = QuizStudent::create([
                'quiz_id' => $quiz->id,
                'student_code' => $reservation->slot_code,
                'student_name' => __('controllers.anonymous_student_name'),
                'max_attempts' => 1,
                'is_anonymous' => true,
                'access_token' => null,
                'access_token_hash' => QuizStudent::generateLinkTokenHash(),
            ]);

            $attempt = $quiz->attempts()->create([
                'quiz_student_id' => $student->id,
                'student_code' => $student->student_code,
                'student_name' => $student->student_name,
                'max_attempts' => 1,
                'score' => 0,
                'status' => QuizAttempt::STATUS_IN_PROGRESS,
                'question_order' => $questionOrder,
                'answer_order' => $answerOrderMap,
                'skipped_question_ids' => $skippedQuestions,
                'current_question_index' => max($currentQuestionIndex, count($questionOrder)),
            ]);

            foreach ($guestAnswers as $questionId => $answerIds) {
                $question = $quiz->questions()->with('answers')->find((int) $questionId);
                if (! $question || ! is_array($answerIds)) {
                    continue;
                }

                foreach (array_unique(array_map('intval', $answerIds)) as $answerId) {
                    $isCorrect = $question->answers->contains(fn ($answer) => (int) $answer->id === $answerId && (bool) $answer->is_correct);

                    QuizAttemptAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'answer_id' => $answerId,
                        'is_correct' => $isCorrect,
                    ]);
                }
            }

            $reservation->delete();

            return $this->attemptLifecycle->submit($attempt, $quiz);
        });
    }

    private function ensurePublicAnonymousPoolReservationIsActive(Quiz $quiz): ?RedirectResponse
    {
        if (! $this->sessionUsesPublicAnonymousPool($quiz)) {
            return null;
        }

        $reservation = $this->currentPublicAnonymousPoolReservation($quiz);

        if (! $reservation) {
            $this->resetQuizRuntimeState();

            return redirect()->route('quiz.join')
                ->with('error', __('join.public_pool_slot_expired'));
        }

        Session::put('student_code', $reservation->slot_code);
        Session::put('student_name', __('controllers.anonymous_student_name'));

        return null;
    }
}
