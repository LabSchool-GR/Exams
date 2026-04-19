<?php

/**
 * QuizDisplaySessionService.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Builds and synchronizes the second-screen runtime without relying on shared browser sessions.
 */
class QuizDisplaySessionService
{
    public function __construct(
        private readonly AttemptLifecycleService $attemptLifecycle
    ) {}

    public function launchForStudent(Quiz $quiz, QuizStudent $student): QuizDisplaySession
    {
        $this->assertQuizSupportsDisplayMode($quiz);
        $this->assertStudentSupportsDisplayMode($quiz, $student);

        $existing = $this->findReusableSession($quiz, $student);
        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($quiz, $student): QuizDisplaySession {
            $this->revokeActiveSessions($student);

            $attempt = $this->resolveOrCreateAttempt($quiz, $student);
            $questionOrder = $this->resolvedQuestionOrder($quiz, $attempt);
            $answerOrderMap = $this->resolvedAnswerOrderMap($quiz, $attempt, $questionOrder);

            if (! $attempt->isInProgress()) {
                throw new \RuntimeException(__('controllers.second_screen_session_not_writable'));
            }

            $attempt = $this->attemptLifecycle->beginAttempt($attempt, $quiz, $questionOrder);
            $attempt = $this->attemptLifecycle->syncRuntimeState(
                $attempt,
                $questionOrder,
                $answerOrderMap,
                [],
                max(0, (int) ($attempt->current_question_index ?? 0))
            );

            return QuizDisplaySession::create([
                'quiz_id' => $quiz->id,
                'quiz_student_id' => $student->id,
                'quiz_attempt_id' => $attempt->id,
                'status' => QuizDisplaySession::STATUS_WAITING,
                'state_version' => 1,
                'expires_at' => $attempt->expires_at ?? now()->addMinutes((int) config('security.signed_urls.display_session_ttl_minutes', 480)),
            ]);
        })->fresh(['quiz', 'student', 'attempt.answers', 'attempt.quiz']);
    }

    public function claimController(QuizDisplaySession $displaySession, string $sessionId): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);

        if (! $displaySession->isActive()) {
            throw new \RuntimeException(__('display.session_unavailable'));
        }

        $sessionHash = hash('sha256', $sessionId);

        if (
            $displaySession->isControllerClaimed()
            && ! hash_equals((string) $displaySession->controller_session_hash, $sessionHash)
        ) {
            throw new \RuntimeException(__('display.session_already_claimed'));
        }

        $updates = [
            'status' => QuizDisplaySession::STATUS_ACTIVE,
            'controller_session_hash' => $sessionHash,
            'controller_last_seen_at' => now(),
            'controller_claimed_at' => $displaySession->controller_claimed_at ?? now(),
        ];

        if (! $displaySession->isControllerClaimed()) {
            $updates['state_version'] = (int) $displaySession->state_version + 1;
        }

        $displaySession->update($updates);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function touchScreen(QuizDisplaySession $displaySession): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);

        $displaySession->update([
            'screen_last_seen_at' => now(),
        ]);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function touchController(QuizDisplaySession $displaySession): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);

        $displaySession->update([
            'controller_last_seen_at' => now(),
        ]);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function syncAnswerSelection(QuizDisplaySession $displaySession, array $selectedAnswerIds): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);
        $attempt = $displaySession->attempt()->with(['quiz.questions.answers', 'answers'])->firstOrFail();

        if (! $attempt->isInProgress()) {
            throw new \RuntimeException(__('display.session_unavailable'));
        }

        $runtime = $this->runtimeContext($displaySession->fresh(['quiz', 'student', 'attempt.answers']), false);
        $question = $runtime['currentQuestion'];

        $allowedAnswerIds = $question->answers->pluck('id')->map(fn ($id) => (int) $id)->all();
        $filteredIds = collect($selectedAnswerIds)
            ->filter(fn ($answerId) => is_numeric($answerId))
            ->map(fn ($answerId) => (int) $answerId)
            ->unique()
            ->values()
            ->all();

        if (array_diff($filteredIds, $allowedAnswerIds)) {
            throw new \RuntimeException(__('display.invalid_selection'));
        }

        DB::transaction(function () use ($attempt, $question, $filteredIds): void {
            $attempt->answers()
                ->where('question_id', $question->id)
                ->delete();

            foreach ($filteredIds as $answerId) {
                $answer = $question->answers->firstWhere('id', $answerId);

                if (! $answer) {
                    continue;
                }

                QuizAttemptAnswer::create([
                    'attempt_id' => $attempt->id,
                    'question_id' => $question->id,
                    'answer_id' => $answerId,
                    'is_correct' => (bool) $answer->is_correct,
                ]);
            }
        });

        $displaySession->update([
            'status' => QuizDisplaySession::STATUS_ACTIVE,
            'controller_last_seen_at' => now(),
            'state_version' => (int) $displaySession->state_version + 1,
        ]);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function navigate(QuizDisplaySession $displaySession, string $direction): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);
        $runtime = $this->runtimeContext($displaySession, false);
        $attempt = $runtime['attempt'];
        $quiz = $runtime['quiz'];
        $questionOrder = $runtime['questionOrder'];
        $currentIndex = $runtime['currentIndex'];
        $currentQuestion = $runtime['currentQuestion'];
        $selectedAnswerIds = $runtime['selectedAnswerIds'];

        if (! $attempt->isInProgress()) {
            throw new \RuntimeException(__('display.session_unavailable'));
        }

        if ($direction === 'previous') {
            if ($currentIndex <= 0) {
                return $displaySession;
            }

            $nextIndex = $currentIndex - 1;
        } elseif ($direction === 'next') {
            if (count($selectedAnswerIds) !== (int) $currentQuestion->correct_answers_count) {
                throw new \RuntimeException(__('display.answer_selection_required'));
            }

            if ($currentIndex >= (count($questionOrder) - 1)) {
                return $displaySession;
            }

            $nextIndex = $currentIndex + 1;
        } else {
            throw new \RuntimeException(__('display.invalid_navigation'));
        }

        $this->attemptLifecycle->syncRuntimeState(
            $attempt,
            $questionOrder,
            is_array($attempt->answer_order) ? $attempt->answer_order : [],
            [],
            $nextIndex
        );

        $displaySession->update([
            'status' => QuizDisplaySession::STATUS_ACTIVE,
            'controller_last_seen_at' => now(),
            'state_version' => (int) $displaySession->state_version + 1,
            'expires_at' => $attempt->fresh()->expires_at ?? $displaySession->expires_at,
        ]);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function submit(QuizDisplaySession $displaySession): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);
        $runtime = $this->runtimeContext($displaySession, false);
        $attempt = $runtime['attempt'];
        $quiz = $runtime['quiz'];
        $questions = $runtime['questions'];

        foreach ($questions as $question) {
            $count = $attempt->answers()
                ->where('question_id', $question->id)
                ->count();

            if ($count !== (int) $question->correct_answers_count) {
                throw new \RuntimeException(__('display.complete_all_questions'));
            }
        }

        $this->attemptLifecycle->submit($attempt, $quiz);

        $displaySession->update([
            'status' => QuizDisplaySession::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'controller_last_seen_at' => now(),
            'state_version' => (int) $displaySession->state_version + 1,
        ]);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function terminate(QuizDisplaySession $displaySession): QuizDisplaySession
    {
        $displaySession = $this->normalizeSessionState($displaySession);
        $attempt = $displaySession->attempt;
        $quiz = $displaySession->quiz;

        if (! $attempt || ! $quiz) {
            throw new \RuntimeException(__('display.session_unavailable'));
        }

        if ($attempt->isInProgress()) {
            $attempt = $this->attemptLifecycle->submit($attempt, $quiz);

            if ($attempt->status === QuizAttempt::STATUS_SUBMITTED) {
                $attempt->update([
                    'finish_reason' => QuizAttempt::FINISH_REASON_ADMIN_TERMINATED,
                ]);

                $attempt = $attempt->fresh();
            }
        }

        $status = match ($attempt->status) {
            QuizAttempt::STATUS_SUBMITTED => QuizDisplaySession::STATUS_SUBMITTED,
            QuizAttempt::STATUS_EXPIRED => QuizDisplaySession::STATUS_EXPIRED,
            default => QuizDisplaySession::STATUS_REVOKED,
        };

        $displaySession->update([
            'status' => $status,
            'submitted_at' => $attempt->submitted_at,
            'state_version' => (int) $displaySession->state_version + 1,
            'expires_at' => $attempt->expires_at ?? $displaySession->expires_at,
        ]);

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }

    public function buildState(QuizDisplaySession $displaySession, bool $forScreen = false): array
    {
        $runtime = $this->runtimeContext($displaySession, $forScreen);
        $quiz = $runtime['quiz'];
        $attempt = $runtime['attempt'];
        $student = $runtime['student'];
        $question = $runtime['currentQuestion'];
        $selectedAnswerIds = $runtime['selectedAnswerIds'];
        $selectedAnswerTexts = $runtime['selectedAnswerTexts'];
        $currentIndex = $runtime['currentIndex'];
        $questionOrder = $runtime['questionOrder'];
        $questions = $runtime['questions'];
        $answers = $runtime['orderedAnswers'];
        $answerCountsByQuestionId = $this->answerCountsByQuestionId($attempt);
        $timeRemaining = $this->attemptLifecycle->secondsRemaining($attempt, $quiz);

        $status = $displaySession->status;
        $statusLabel = match ($status) {
            QuizDisplaySession::STATUS_WAITING => __('display.status_waiting'),
            QuizDisplaySession::STATUS_ACTIVE => __('display.status_active'),
            QuizDisplaySession::STATUS_SUBMITTED => __('display.status_submitted'),
            QuizDisplaySession::STATUS_EXPIRED => __('display.status_expired'),
            default => __('display.status_revoked'),
        };

        return [
            'session' => [
                'id' => $displaySession->id,
                'status' => $status,
                'status_label' => $statusLabel,
                'state_version' => (int) $displaySession->state_version,
                'controller_claimed' => $displaySession->isControllerClaimed(),
            ],
            'quiz' => [
                'id' => $quiz->id,
                'title' => $quiz->title,
            ],
            'student' => [
                'id' => $student?->id,
                'name' => $student?->student_name ?? $attempt->student_name,
                'code' => $student?->student_code ?? $attempt->student_code,
            ],
            'attempt' => [
                'id' => $attempt->id,
                'score' => $attempt->score,
                'status' => $attempt->status,
                'finish_reason' => $attempt->finish_reason,
                'submitted_at' => optional($attempt->submitted_at)?->toIso8601String(),
                'time_remaining_seconds' => $timeRemaining,
            ],
            'progress' => [
                'current' => count($questionOrder) > 0 ? min($currentIndex + 1, count($questionOrder)) : 0,
                'total' => count($questionOrder),
                'completed_questions' => $this->completedQuestionsCount($questions, $answerCountsByQuestionId),
                'label' => __('join.question_number', [
                    'current' => count($questionOrder) > 0 ? min($currentIndex + 1, count($questionOrder)) : 0,
                    'total' => count($questionOrder),
                ]),
            ],
            'question' => $question ? [
                'id' => $question->id,
                'text' => $question->text,
                'image_url' => $question->image ? asset('storage/'.$question->image) : null,
                'required_answers' => (int) $question->correct_answers_count,
                'instruction' => trans_choice('join.select_instruction', (int) $question->correct_answers_count, [
                    'count' => (int) $question->correct_answers_count,
                ]),
                'answers' => array_map(
                    fn (array $answer) => array_merge($answer, [
                        'is_selected' => in_array($answer['id'], $selectedAnswerIds, true),
                    ]),
                    $answers
                ),
                'selected_answer_ids' => $selectedAnswerIds,
                'selected_answer_texts' => $selectedAnswerTexts,
                'selection_summary' => empty($selectedAnswerTexts)
                    ? __('display.no_answer_selected')
                    : implode(', ', $selectedAnswerTexts),
            ] : null,
            'actions' => [
                'can_previous' => $question !== null && $currentIndex > 0 && $attempt->isInProgress(),
                'can_next' => $question !== null
                    && $currentIndex < (count($questionOrder) - 1)
                    && count($selectedAnswerIds) === (int) ($question->correct_answers_count ?? 0)
                    && $attempt->isInProgress(),
                'can_submit' => $attempt->isInProgress() && $this->canSubmitAttempt($attempt, $questions, $answerCountsByQuestionId),
                'submit_label' => __('join.submit_quiz'),
                'previous_label' => __('quizzes.previous_question'),
                'next_label' => __('quizzes.next_question'),
                'helper_text' => $this->actionHelperText(
                    $attempt,
                    $questions,
                    $answerCountsByQuestionId,
                    $question,
                    $currentIndex,
                    $questionOrder,
                    $selectedAnswerIds
                ),
            ],
            'messages' => [
                'screen_waiting' => __('display.screen_waiting'),
                'screen_connected' => __('display.screen_connected'),
                'screen_completed' => __('display.screen_completed'),
                'screen_expired' => __('display.screen_expired'),
                'controller_ready' => __('display.controller_ready'),
            ],
            'result' => [
                'pdf_url' => $this->resultPdfUrl($quiz, $attempt),
                'reason_label' => $this->resultReasonLabel($displaySession, $attempt),
            ],
        ];
    }

    private function findReusableSession(Quiz $quiz, QuizStudent $student): ?QuizDisplaySession
    {
        $session = QuizDisplaySession::query()
            ->where('quiz_id', $quiz->id)
            ->where('quiz_student_id', $student->id)
            ->whereIn('status', [
                QuizDisplaySession::STATUS_WAITING,
                QuizDisplaySession::STATUS_ACTIVE,
            ])
            ->with(['quiz', 'student', 'attempt.answers'])
            ->latest('id')
            ->first();

        if (! $session) {
            return null;
        }

        $session = $this->normalizeSessionState($session);

        if ($session->isActive() && $session->attempt?->isInProgress()) {
            return $session->fresh(['quiz', 'student', 'attempt.answers']);
        }

        return null;
    }

    private function revokeActiveSessions(QuizStudent $student): void
    {
        QuizDisplaySession::query()
            ->where('quiz_student_id', $student->id)
            ->whereIn('status', [QuizDisplaySession::STATUS_WAITING, QuizDisplaySession::STATUS_ACTIVE])
            ->update([
                'status' => QuizDisplaySession::STATUS_REVOKED,
                'state_version' => DB::raw('state_version + 1'),
            ]);
    }

    private function resolveOrCreateAttempt(Quiz $quiz, QuizStudent $student): QuizAttempt
    {
        $attempts = QuizAttempt::query()
            ->where('quiz_id', $quiz->id)
            ->where(function ($query) use ($student): void {
                $query->where('quiz_student_id', $student->id)
                    ->orWhere(function ($legacyQuery) use ($student): void {
                        $legacyQuery->whereNull('quiz_student_id')
                            ->where('student_code', $student->student_code);
                    });
            })
            ->orderByDesc('created_at')
            ->get();

        $maxAllowed = $student->max_attempts ?? 1;
        $completed = $attempts->whereNotNull('submitted_at')->count();

        if ($completed >= $maxAllowed) {
            throw new \RuntimeException(__('controllers.second_screen_attempt_limit_reached'));
        }

        $ongoing = $attempts->first(fn (QuizAttempt $attempt) => $attempt->submitted_at === null);

        if ($ongoing) {
            $ongoing = $this->attemptLifecycle->expireIfNeeded($ongoing, $quiz);

            if ($ongoing->isInProgress() && ! $quiz->allow_resume) {
                $ongoing->answers()->delete();
                $this->attemptLifecycle->abandon($ongoing, true);
                $ongoing = null;
            }
        }

        if ($ongoing && $ongoing->isInProgress()) {
            return $ongoing->fresh();
        }

        return $quiz->attempts()->create([
            'quiz_student_id' => $student->id,
            'student_code' => $student->student_code,
            'student_name' => $student->student_name,
            'max_attempts' => $maxAllowed,
            'score' => 0,
            'status' => QuizAttempt::STATUS_IN_PROGRESS,
            'current_question_index' => 0,
        ]);
    }

    private function resolvedQuestionOrder(Quiz $quiz, QuizAttempt $attempt): array
    {
        $stored = collect($attempt->question_order ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($stored !== []) {
            return $stored;
        }

        $questionIds = $quiz->questions()
            ->orderBy('order')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($questionIds === []) {
            throw new \RuntimeException(__('controllers.second_screen_no_questions'));
        }

        if ($quiz->is_random_order) {
            shuffle($questionIds);

            if ($quiz->questions_limit) {
                $questionIds = array_slice($questionIds, 0, (int) $quiz->questions_limit);
            }
        }

        return array_values($questionIds);
    }

    private function resolvedAnswerOrderMap(Quiz $quiz, QuizAttempt $attempt, array $questionOrder): array
    {
        $stored = is_array($attempt->answer_order) ? $attempt->answer_order : [];
        $questions = Question::query()
            ->with('answers')
            ->whereIn('id', $questionOrder)
            ->get()
            ->keyBy('id');

        $map = [];

        foreach ($questionOrder as $questionId) {
            $question = $questions->get($questionId);
            if (! $question) {
                continue;
            }

            $currentAnswerIds = $question->answers->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            $storedAnswerIds = $stored[$questionId] ?? null;

            if (
                is_array($storedAnswerIds)
                && count($storedAnswerIds) === count($currentAnswerIds)
                && empty(array_diff($storedAnswerIds, $currentAnswerIds))
                && empty(array_diff($currentAnswerIds, $storedAnswerIds))
            ) {
                $map[$questionId] = array_values(array_map('intval', $storedAnswerIds));

                continue;
            }

            $ordered = $currentAnswerIds;

            if ($quiz->is_random_answers_order) {
                shuffle($ordered);
            }

            $map[$questionId] = $ordered;
        }

        return $map;
    }

    private function runtimeContext(QuizDisplaySession $displaySession, bool $persistLifecycle): array
    {
        $displaySession->loadMissing(['quiz.questions.answers', 'student', 'attempt.answers']);
        $quiz = $displaySession->quiz;
        $student = $displaySession->student;
        $attempt = $displaySession->attempt;

        if (! $quiz || ! $attempt) {
            throw new \RuntimeException(__('display.session_unavailable'));
        }

        $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);

        if ($persistLifecycle && ! $attempt->isInProgress() && $displaySession->status === QuizDisplaySession::STATUS_ACTIVE) {
            $displaySession->update([
                'status' => $attempt->status === QuizAttempt::STATUS_SUBMITTED
                    ? QuizDisplaySession::STATUS_SUBMITTED
                    : QuizDisplaySession::STATUS_EXPIRED,
                'submitted_at' => $attempt->submitted_at,
                'state_version' => (int) $displaySession->state_version + 1,
            ]);

            $displaySession = $displaySession->fresh(['quiz.questions.answers', 'student', 'attempt.answers']);
            $attempt = $displaySession->attempt;
            $quiz = $displaySession->quiz;
            $student = $displaySession->student;
        }

        $questionOrder = collect($attempt->question_order ?? [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $questions = $quiz->questions
            ->whereIn('id', $questionOrder)
            ->keyBy('id');

        $currentIndex = count($questionOrder) > 0
            ? max(0, min((int) ($attempt->current_question_index ?? 0), count($questionOrder) - 1))
            : 0;
        $currentQuestionId = $questionOrder[$currentIndex] ?? null;
        $currentQuestion = $currentQuestionId ? $questions->get($currentQuestionId) : null;

        $selectedAnswerIds = $currentQuestion
            ? $attempt->answers
                ->where('question_id', $currentQuestion->id)
                ->pluck('answer_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
            : [];

        $selectedAnswerTexts = $currentQuestion
            ? $currentQuestion->answers
                ->whereIn('id', $selectedAnswerIds)
                ->pluck('text')
                ->values()
                ->all()
            : [];

        $answerOrderMap = is_array($attempt->answer_order) ? $attempt->answer_order : [];
        $orderedAnswers = [];

        if ($currentQuestion) {
            $answersById = $currentQuestion->answers->keyBy('id');
            $orderedAnswerIds = $answerOrderMap[$currentQuestion->id] ?? $currentQuestion->answers->pluck('id')->all();

            foreach ($orderedAnswerIds as $index => $answerId) {
                $answer = $answersById->get($answerId);

                if (! $answer) {
                    continue;
                }

                $orderedAnswers[] = [
                    'id' => (int) $answer->id,
                    'prefix' => $this->answerPrefix($quiz, $index),
                    'text' => $answer->text,
                ];
            }
        }

        return [
            'displaySession' => $displaySession,
            'quiz' => $quiz,
            'student' => $student,
            'attempt' => $attempt,
            'questionOrder' => $questionOrder,
            'questions' => $questions->values(),
            'currentIndex' => $currentIndex,
            'currentQuestion' => $currentQuestion,
            'orderedAnswers' => $orderedAnswers,
            'selectedAnswerIds' => $selectedAnswerIds,
            'selectedAnswerTexts' => $selectedAnswerTexts,
        ];
    }

    private function answerPrefix(Quiz $quiz, int $index): ?string
    {
        if (! $quiz->show_answer_numbering) {
            return null;
        }

        $effectiveLanguage = $quiz->language === 'auto'
            ? app()->getLocale()
            : ($quiz->language ?? app()->getLocale());

        $alphabet = $effectiveLanguage === 'en'
            ? range('A', 'Z')
            : ['Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω'];

        return ($alphabet[$index] ?? (string) ($index + 1)).'.';
    }

    private function assertQuizSupportsDisplayMode(Quiz $quiz): void
    {
        if (! $quiz->usesSecondScreenMode()) {
            throw new \RuntimeException(__('controllers.second_screen_disabled'));
        }

        if ($quiz->usesLearningMode() || $quiz->is_anonymous_bulk_mode || $quiz->is_public_anonymous_pool_mode) {
            throw new \RuntimeException(__('controllers.second_screen_conflict'));
        }
    }

    private function assertStudentSupportsDisplayMode(Quiz $quiz, QuizStudent $student): void
    {
        if ($student->quiz_id !== $quiz->id) {
            throw new \RuntimeException(__('controllers.student_not_found'));
        }

        if ((bool) $student->is_anonymous) {
            throw new \RuntimeException(__('controllers.second_screen_registered_only'));
        }
    }

    private function answerCountsByQuestionId(QuizAttempt $attempt): array
    {
        return $attempt->answers
            ->groupBy('question_id')
            ->mapWithKeys(fn (Collection $answers, $questionId): array => [(int) $questionId => $answers->count()])
            ->all();
    }

    private function completedQuestionsCount(Collection $questions, array $answerCountsByQuestionId): int
    {
        return $questions->filter(
            fn (Question $question): bool => ($answerCountsByQuestionId[$question->id] ?? 0) === (int) $question->correct_answers_count
        )->count();
    }

    private function canSubmitAttempt(QuizAttempt $attempt, Collection $questions, array $answerCountsByQuestionId): bool
    {
        if (! $attempt->isInProgress()) {
            return false;
        }

        return $questions->every(
            fn (Question $question): bool => ($answerCountsByQuestionId[$question->id] ?? 0) === (int) $question->correct_answers_count
        );
    }

    private function resultPdfUrl(Quiz $quiz, QuizAttempt $attempt): ?string
    {
        if (! $attempt->isFinalized()) {
            return null;
        }

        return URL::temporarySignedRoute(
            'quiz_attempts.download_pdf_signed',
            now()->addMinutes((int) config('security.signed_urls.attempt_pdf_ttl_minutes', 1440)),
            [$quiz, $attempt]
        );
    }

    private function actionHelperText(
        QuizAttempt $attempt,
        Collection $questions,
        array $answerCountsByQuestionId,
        ?Question $question,
        int $currentIndex,
        array $questionOrder,
        array $selectedAnswerIds
    ): ?string {
        if (! $attempt->isInProgress() || ! $question) {
            return null;
        }

        $requiredAnswers = (int) $question->correct_answers_count;
        $selectedCount = count($selectedAnswerIds);
        $isLastQuestion = $currentIndex >= (count($questionOrder) - 1);

        if ($selectedCount !== $requiredAnswers) {
            return trans_choice('display.action_helper_select_answers', $requiredAnswers, [
                'count' => $requiredAnswers,
            ]);
        }

        if ($isLastQuestion && ! $this->canSubmitAttempt($attempt, $questions, $answerCountsByQuestionId)) {
            return __('display.action_helper_complete_all');
        }

        return null;
    }

    private function resultReasonLabel(QuizDisplaySession $displaySession, QuizAttempt $attempt): ?string
    {
        return match (true) {
            $attempt->finish_reason === QuizAttempt::FINISH_REASON_ADMIN_TERMINATED => __('display.result_reason_admin_terminated'),
            $attempt->finish_reason === QuizAttempt::FINISH_REASON_TIMER_EXPIRED,
            $displaySession->status === QuizDisplaySession::STATUS_EXPIRED => __('display.result_reason_timer_expired'),
            $attempt->status === QuizAttempt::STATUS_SUBMITTED => __('display.result_reason_submitted'),
            $displaySession->status === QuizDisplaySession::STATUS_REVOKED => __('display.result_reason_revoked'),
            default => null,
        };
    }

    private function normalizeSessionState(QuizDisplaySession $displaySession): QuizDisplaySession
    {
        $displaySession->loadMissing(['quiz', 'student', 'attempt.answers']);

        $attempt = $displaySession->attempt;
        $quiz = $displaySession->quiz;

        if (! $attempt || ! $quiz) {
            throw new \RuntimeException(__('display.session_unavailable'));
        }

        $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);

        if (! $attempt->isInProgress() && $displaySession->isActive()) {
            $displaySession->update([
                'status' => $attempt->status === QuizAttempt::STATUS_SUBMITTED
                    ? QuizDisplaySession::STATUS_SUBMITTED
                    : QuizDisplaySession::STATUS_EXPIRED,
                'submitted_at' => $attempt->submitted_at,
                'state_version' => (int) $displaySession->state_version + 1,
            ]);
        } elseif ($displaySession->expires_at && $displaySession->expires_at->isPast() && $displaySession->isActive()) {
            $displaySession->update([
                'status' => QuizDisplaySession::STATUS_EXPIRED,
                'state_version' => (int) $displaySession->state_version + 1,
            ]);
        }

        return $displaySession->fresh(['quiz', 'student', 'attempt.answers']);
    }
}
