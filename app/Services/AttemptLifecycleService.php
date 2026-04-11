<?php

/**
 * AttemptLifecycleService.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;

/**
 * Centralizes state transitions for quiz attempts so timing and scoring rules stay consistent.
 */
class AttemptLifecycleService
{
    /**
     * Start the attempt officially on the server and persist its deterministic runtime state.
     */
    public function beginAttempt(QuizAttempt $attempt, Quiz $quiz, array $questionOrder): QuizAttempt
    {
        $updates = [
            'status' => QuizAttempt::STATUS_IN_PROGRESS,
            'question_order' => array_values($questionOrder),
            'current_question_index' => $attempt->current_question_index ?? 0,
            'last_seen_at' => now(),
        ];

        if ($attempt->started_at === null) {
            $updates['started_at'] = now();

            if ($quiz->has_timer) {
                $updates['expires_at'] = now()->addSeconds($quiz->time_limit);
            }
        }

        $attempt->update($updates);

        return $attempt->fresh();
    }

    /**
     * Persist the current in-progress runtime state.
     */
    public function syncRuntimeState(
        QuizAttempt $attempt,
        array $questionOrder,
        array $answerOrderMap = [],
        array $skippedQuestionIds = [],
        ?int $currentQuestionIndex = null
    ): QuizAttempt {
        if (!$attempt->isInProgress()) {
            return $attempt;
        }

        $attempt->update([
            'question_order' => array_values($questionOrder),
            'answer_order' => $answerOrderMap,
            'skipped_question_ids' => array_values(array_unique($skippedQuestionIds)),
            'current_question_index' => $currentQuestionIndex ?? $attempt->current_question_index,
            'last_seen_at' => now(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Update the heartbeat timestamp of an active attempt.
     */
    public function touch(QuizAttempt $attempt): void
    {
        if (!$attempt->isInProgress()) {
            return;
        }

        $attempt->update([
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Close the attempt as expired when the authoritative deadline has passed.
     */
    public function expireIfNeeded(QuizAttempt $attempt, Quiz $quiz): QuizAttempt
    {
        if (
            !$attempt->isInProgress() ||
            !$quiz->has_timer ||
            $attempt->expires_at === null ||
            $attempt->expires_at->isFuture()
        ) {
            return $attempt;
        }

        return $this->finalize(
            $attempt,
            $quiz,
            QuizAttempt::STATUS_EXPIRED,
            QuizAttempt::FINISH_REASON_TIMER_EXPIRED
        );
    }

    /**
     * Close the attempt as manually submitted and freeze its final score.
     */
    public function submit(QuizAttempt $attempt, Quiz $quiz): QuizAttempt
    {
        if (!$attempt->isInProgress()) {
            return $attempt;
        }

        return $this->finalize(
            $attempt,
            $quiz,
            QuizAttempt::STATUS_SUBMITTED,
            QuizAttempt::FINISH_REASON_MANUAL_SUBMIT
        );
    }

    /**
     * Close an in-progress attempt that can no longer be resumed.
     */
    public function abandon(QuizAttempt $attempt, bool $consumeAttempt = true): QuizAttempt
    {
        if ($attempt->isFinalized()) {
            return $attempt;
        }

        $attempt->update([
            'status' => QuizAttempt::STATUS_ABANDONED,
            'finish_reason' => QuizAttempt::FINISH_REASON_ABANDONED,
            'score' => 0,
            'submitted_at' => $consumeAttempt ? ($attempt->submitted_at ?? now()) : $attempt->submitted_at,
            'finalized_at' => $attempt->finalized_at ?? now(),
            'last_seen_at' => now(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Return the current time remaining in seconds for active timed attempts.
     */
    public function secondsRemaining(QuizAttempt $attempt, Quiz $quiz): ?int
    {
        if (!$quiz->has_timer || $attempt->expires_at === null) {
            return null;
        }

        return max(0, now()->diffInSeconds($attempt->expires_at, false));
    }

    /**
     * Persist a terminal state together with the frozen score snapshot.
     */
    private function finalize(QuizAttempt $attempt, Quiz $quiz, string $status, string $reason): QuizAttempt
    {
        [$correctCount, $totalQuestions, $scorePercentage] = $this->calculateScore($attempt, $quiz);

        $attempt->update([
            'status' => $status,
            'finish_reason' => $reason,
            'score' => round($scorePercentage, 2),
            'submitted_at' => $attempt->submitted_at ?? now(),
            'finalized_at' => $attempt->finalized_at ?? now(),
            'last_seen_at' => now(),
        ]);

        return $attempt->fresh();
    }

    /**
     * Compute the score from the persisted answers and authoritative question order.
     */
    private function calculateScore(QuizAttempt $attempt, Quiz $quiz): array
    {
        $questionIds = collect($attempt->question_order)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if (empty($questionIds)) {
            $questionIds = $quiz->questions()->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        $questions = $quiz->questions()
            ->with('answers')
            ->whereIn('id', $questionIds)
            ->get()
            ->keyBy('id');

        $correctCount = 0;

        foreach ($questionIds as $questionId) {
            $question = $questions->get($questionId);
            if (!$question) {
                continue;
            }

            $correctIds = $question->answers
                ->where('is_correct', true)
                ->pluck('id')
                ->sort()
                ->values()
                ->all();

            $selectedIds = $attempt->answers()
                ->where('question_id', $questionId)
                ->pluck('answer_id')
                ->sort()
                ->values()
                ->all();

            if (
                !empty($selectedIds) &&
                count($correctIds) === count($selectedIds) &&
                empty(array_diff($correctIds, $selectedIds))
            ) {
                $correctCount++;
            }
        }

        $totalQuestions = count($questionIds);
        $scorePercentage = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;

        return [$correctCount, $totalQuestions, $scorePercentage];
    }
}