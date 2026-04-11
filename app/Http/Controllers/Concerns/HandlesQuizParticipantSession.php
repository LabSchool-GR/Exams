<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;

trait HandlesQuizParticipantSession
{
    private function ensureQuizRouteToken(Quiz $quiz): string
    {
        if (Session::get('quiz_route_quiz_id') !== $quiz->id || !Session::has('quiz_route_token')) {
            Session::put('quiz_route_quiz_id', $quiz->id);
            Session::put('quiz_route_token', Str::random(24));
        }

        return Session::get('quiz_route_token');
    }

    private function resetQuizRuntimeState(): void
    {
        Session::forget([
            'quiz_id',
            'quiz_code',
            'attempt_id',
            'student_code',
            'student_name',
            'question_order',
            'question_route_map',
            'quiz_end_time',
            'skipped_questions',
            'answer_order_map',
            'guest_answers',
            'guest_answered',
            'guest_started_at',
            'public_anonymous_pool_active',
            'public_anonymous_pool_reservation_id',
            'public_anonymous_pool_slot_code',
            'current_question_index',
            'quiz_route_quiz_id',
            'quiz_route_token',
            'learning_mode_active',
            'learning_feedback_question_id',
            'learning_feedback_selected_answers',
            'learning_feedback_correct_answers',
        ]);
    }

    private function syncSessionForLearningMode(
        Quiz $quiz,
        string $studentCode,
        string $studentName,
        bool $resetRuntimeState = true
    ): void {
        if ($resetRuntimeState) {
            $this->resetQuizRuntimeState();
        }

        Session::put('quiz_id', $quiz->id);
        Session::put('attempt_id', 'guest');
        Session::put('student_code', $studentCode);
        Session::put('student_name', $studentName);
        Session::put('guest_started_at', now());
        Session::put('learning_mode_active', true);

        $this->clearLearningModeFeedback();
        $this->ensureQuizRouteToken($quiz);
    }

    private function sessionUsesLearningMode(Quiz $quiz): bool
    {
        return $quiz->usesLearningMode()
            && (bool) Session::get('learning_mode_active', false);
    }

    private function clearLearningModeFeedback(): void
    {
        Session::forget([
            'learning_feedback_question_id',
            'learning_feedback_selected_answers',
            'learning_feedback_correct_answers',
        ]);
    }

    private function storeLearningModeFeedback(Question $question, array $selectedAnswerIds): void
    {
        $selectedAnswerIds = array_values(array_unique(array_map('intval', $selectedAnswerIds)));
        sort($selectedAnswerIds);

        $correctAnswerIds = $question->answers()
            ->where('is_correct', true)
            ->pluck('id')
            ->map(fn ($answerId) => (int) $answerId)
            ->sort()
            ->values()
            ->all();

        Session::put('learning_feedback_question_id', $question->id);
        Session::put('learning_feedback_selected_answers', $selectedAnswerIds);
        Session::put('learning_feedback_correct_answers', $correctAnswerIds);
    }

    private function currentLearningModeFeedback(Question $question): ?array
    {
        if ((int) Session::get('learning_feedback_question_id', 0) !== (int) $question->id) {
            return null;
        }

        $selectedAnswerIds = array_values(array_map(
            'intval',
            Session::get('learning_feedback_selected_answers', [])
        ));
        $correctAnswerIds = array_values(array_map(
            'intval',
            Session::get('learning_feedback_correct_answers', [])
        ));

        sort($selectedAnswerIds);
        sort($correctAnswerIds);

        return [
            'selectedAnswerIds' => $selectedAnswerIds,
            'correctAnswerIds' => $correctAnswerIds,
            'isCorrect' => count($selectedAnswerIds) === count($correctAnswerIds)
                && empty(array_diff($correctAnswerIds, $selectedAnswerIds)),
        ];
    }

    private function syncSessionFromAttempt(QuizAttempt $attempt): void
    {
        Session::put('attempt_id', $attempt->id);

        if (is_array($attempt->question_order) && !empty($attempt->question_order)) {
            Session::put('question_order', $attempt->question_order);
        }

        if (is_array($attempt->answer_order)) {
            Session::put('answer_order_map', $attempt->answer_order);
        }

        if (is_array($attempt->skipped_question_ids)) {
            Session::put('skipped_questions', $attempt->skipped_question_ids);
        }

        Session::put('current_question_index', max(0, (int) ($attempt->current_question_index ?? 0)));

        if ($attempt->expires_at) {
            Session::put('quiz_end_time', $attempt->expires_at->timestamp);
        } else {
            Session::forget('quiz_end_time');
        }
    }

    private function persistAttemptRuntimeState(QuizAttempt $attempt, ?int $currentQuestionIndex = null): QuizAttempt
    {
        return $this->attemptLifecycle->syncRuntimeState(
            $attempt,
            Session::get('question_order', []),
            Session::get('answer_order_map', []),
            Session::get('skipped_questions', []),
            $currentQuestionIndex
        );
    }

    private function getCurrentQuestionIndex(): int
    {
        return max(0, (int) Session::get('current_question_index', 0));
    }

    private function setCurrentQuestionIndex(int $index): void
    {
        Session::put('current_question_index', max(0, $index));
    }

    private function getExpectedQuestionId(array $questionOrder): ?int
    {
        $currentIndex = $this->getCurrentQuestionIndex();

        if ($currentIndex < count($questionOrder)) {
            $questionId = $questionOrder[$currentIndex] ?? null;
        } else {
            $questionId = Session::get('skipped_questions', [])[0] ?? null;
        }

        return $questionId !== null ? (int) $questionId : null;
    }

    private function isReviewPass(array $questionOrder): bool
    {
        return $this->getCurrentQuestionIndex() >= count($questionOrder);
    }

    private function isLastQuestionInFlow(array $questionOrder): bool
    {
        $skippedQuestions = array_values(array_unique(array_map(
            'intval',
            Session::get('skipped_questions', [])
        )));

        $currentIndex = $this->getCurrentQuestionIndex();
        $totalQuestions = count($questionOrder);

        if ($currentIndex < $totalQuestions) {
            if ($currentIndex < ($totalQuestions - 1)) {
                return false;
            }

            return count($skippedQuestions) === 0;
        }

        return count($skippedQuestions) <= 1;
    }

    private function buildQuestionProgressLabel(bool $isReviewPass, int $displayQuestionIndex, int $totalQuestions): string
    {
        if ($isReviewPass) {
            return __('join.review_skipped_question');
        }

        return __('join.question_number', [
            'current' => $displayQuestionIndex + 1,
            'total' => $totalQuestions,
        ]);
    }

    private function redirectToCurrentQuestionOrEnd(Quiz $quiz, array $questionOrder): RedirectResponse
    {
        $expectedQuestionId = $this->getExpectedQuestionId($questionOrder);

        if ($expectedQuestionId === null) {
            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        return redirect()->route('quiz.question', [
            'quizKey' => $this->ensureQuizRouteToken($quiz),
            'questionKey' => $this->getQuestionRouteToken($expectedQuestionId),
        ]);
    }

    private function enforceCurrentQuestion(Quiz $quiz, Question $question, array $questionOrder): ?RedirectResponse
    {
        $expectedQuestionId = $this->getExpectedQuestionId($questionOrder);

        if ($expectedQuestionId === null) {
            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        if ($question->id !== $expectedQuestionId) {
            return $this->redirectToCurrentQuestionOrEnd($quiz, $questionOrder);
        }

        return null;
    }

    private function advanceAttemptQuestionIndex(?QuizAttempt $attempt, int $currentQuestionId, bool $markSkipped = false): ?QuizAttempt
    {
        $questionOrder = Session::get('question_order', []);
        $currentIndex = $this->getCurrentQuestionIndex();
        $expectedQuestionId = $this->getExpectedQuestionId($questionOrder);

        if ((int) $expectedQuestionId !== $currentQuestionId) {
            return $attempt;
        }

        $skippedQuestions = array_values(array_unique(array_map(
            'intval',
            Session::get('skipped_questions', [])
        )));

        if ($markSkipped) {
            if (!in_array($currentQuestionId, $skippedQuestions, true)) {
                $skippedQuestions[] = $currentQuestionId;
            }
        } else {
            $skippedQuestions = array_values(array_diff($skippedQuestions, [$currentQuestionId]));
        }

        Session::put('skipped_questions', $skippedQuestions);

        $nextIndex = $currentIndex < count($questionOrder)
            ? min($currentIndex + 1, count($questionOrder))
            : $currentIndex;
        $this->setCurrentQuestionIndex($nextIndex);

        if ($attempt) {
            return $this->persistAttemptRuntimeState($attempt, $nextIndex);
        }

        return null;
    }

    private function resolveQuizFromRoute(string $quizKey): Quiz|RedirectResponse
    {
        $sessionQuizId = Session::get('quiz_id');
        $sessionQuizToken = Session::get('quiz_route_token');
        $sessionQuizRouteId = Session::get('quiz_route_quiz_id');

        if (!$sessionQuizId || !$sessionQuizToken || !$sessionQuizRouteId) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.no_session'));
        }

        if (!hash_equals($sessionQuizToken, $quizKey) || (int) $sessionQuizRouteId !== (int) $sessionQuizId) {
            return redirect()->route('quiz.session_conflict');
        }

        $quiz = Quiz::find($sessionQuizId);

        if (!$quiz) {
            $this->resetQuizRuntimeState();

            return redirect()->route('quiz.join')
                ->with('error', __('join.quiz_not_found'));
        }

        return $quiz;
    }

    private function ensureQuestionRouteMap(array $questionIds): array
    {
        $existingIds = Session::get('question_order', []);
        $existingMap = Session::get('question_route_map', []);

        if ($existingIds !== $questionIds || count($existingMap) !== count($questionIds)) {
            $map = [];

            foreach ($questionIds as $questionId) {
                $map[Str::random(24)] = $questionId;
            }

            Session::put('question_route_map', $map);

            return $map;
        }

        return $existingMap;
    }

    private function getQuestionRouteToken(int $questionId): string
    {
        foreach (Session::get('question_route_map', []) as $token => $mappedId) {
            if ((int) $mappedId === $questionId) {
                return $token;
            }
        }

        abort(404);
    }

    private function resolveQuestionFromRoute(string $questionKey, Quiz $quiz): Question
    {
        $questionId = Session::get('question_route_map', [])[$questionKey] ?? null;

        if (!$questionId) {
            abort(404);
        }

        $question = Question::findOrFail($questionId);

        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }

        return $question;
    }

    private function getOrderedAnswersForQuestion(Quiz $quiz, Question $question)
    {
        $answers = $question->relationLoaded('answers')
            ? $question->answers->values()
            : $question->answers()->get()->values();

        if (!$quiz->is_random_answers_order) {
            return $answers;
        }

        $answerOrderMap = Session::get('answer_order_map', []);
        $currentAnswerIds = $answers->pluck('id')->values()->all();
        $storedAnswerIds = $answerOrderMap[$question->id] ?? null;

        if (
            !is_array($storedAnswerIds) ||
            count($storedAnswerIds) !== count($currentAnswerIds) ||
            array_diff($storedAnswerIds, $currentAnswerIds) ||
            array_diff($currentAnswerIds, $storedAnswerIds)
        ) {
            $storedAnswerIds = $currentAnswerIds;
            shuffle($storedAnswerIds);
            $answerOrderMap[$question->id] = $storedAnswerIds;
            Session::put('answer_order_map', $answerOrderMap);
        }

        $answersById = $answers->keyBy('id');

        return collect($storedAnswerIds)
            ->map(fn (int $answerId) => $answersById->get($answerId))
            ->filter()
            ->values();
    }

    /**
     * Render the result screen with fallback view logic.
     */
    private function renderResultView(
        Quiz $quiz,
        QuizAttempt $attempt,
        int $total,
        int $correct,
        float $score,
        array $extraViewData = []
    ): View
    {
        $viewName = $this->resolveParticipantView($quiz, 'result');

        $remainingAttempts = null;

        if ($attempt->student_code !== '0000') {
            $completedQuery = QuizAttempt::query()->whereNotNull('submitted_at');

            if ($attempt->quiz_student_id) {
                $completedQuery->where('quiz_student_id', $attempt->quiz_student_id);
            } else {
                $completedQuery
                    ->where('quiz_id', $quiz->id)
                    ->where('student_code', $attempt->student_code);
            }

            $completed = $completedQuery->count();

            $remainingAttempts = max(0, $attempt->max_attempts - $completed);
        }

        return view($viewName, array_merge([
            'quiz' => $quiz,
            'attempt' => $attempt,
            'totalQuestions' => $total,
            'correctCount' => $correct,
            'scorePercentage' => $score,
            'remainingAttempts' => $remainingAttempts,
        ], $extraViewData));
    }
}
