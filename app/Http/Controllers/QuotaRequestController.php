<?php

/**
 * QuotaRequestController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Mail\QuotaIncreaseRequestMail;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizStudent;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Handles teacher requests for higher resource quotas and forwards them to administrators.
 */
class QuotaRequestController extends Controller
{
    /**
     * Validate, throttle, and email a quota increase request to platform administrators.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'resource_type' => 'required|in:quizzes,questions,answers,students',
            'quiz_id' => 'nullable|integer|exists:quizzes,id',
            'question_id' => 'nullable|integer|exists:questions,id',
        ]);

        $user = $request->user();
        if (!$user || $user->isAdmin()) {
            return back()->with('error', __('controllers.quota_request_not_allowed'));
        }

        [$resourceLabel, $currentLimit, $currentUsage, $quiz, $question] = $this->resolveQuotaContext(
            $user,
            (string) $request->string('resource_type'),
            $request->integer('quiz_id') ?: null,
            $request->integer('question_id') ?: null
        );

        $cacheKey = sprintf(
            'quota_request:%d:%s:%s:%s',
            $user->id,
            (string) $request->string('resource_type'),
            $quiz?->id ?? 'none',
            $question?->id ?? 'none'
        );

        if (!Cache::add($cacheKey, now()->timestamp, now()->addHours(6))) {
            return back()->with('error', __('controllers.quota_request_throttled'));
        }

        $adminEmails = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($adminEmails)) {
            Cache::forget($cacheKey);

            return back()->with('error', __('controllers.quota_request_no_admins'));
        }

        try {
            Mail::to($adminEmails)->queue(new QuotaIncreaseRequestMail([
                'resource_label' => $resourceLabel,
                'resource_type' => (string) $request->string('resource_type'),
                'user_name' => $user->name,
                'user_email' => $user->email,
                'current_limit' => $currentLimit,
                'current_usage' => $currentUsage,
                'user_profile_url' => route('users.show', $user),
                'quiz_title' => $quiz?->title,
                'quiz_edit_url' => $quiz ? route('quizzes.edit', $quiz) : null,
                'question_text' => $question?->text,
                'question_edit_url' => ($quiz && $question) ? route('quizzes.questions.edit', [$quiz, $question]) : null,
                'users_url' => route('users.index'),
            ]));
        } catch (\Throwable $exception) {
            Cache::forget($cacheKey);

            Log::error('Quota request notification queue dispatch failed.', [
                'message' => $exception->getMessage(),
                'user_id' => $user->id,
                'resource_type' => (string) $request->string('resource_type'),
            ]);

            return back()->with('error', __('controllers.quota_request_send_failed'));
        }

        return back()->with('success', __('controllers.quota_request_sent'));
    }

    /**
     * Resolve the quota label, current limit, and current usage for the requested resource type.
     *
     * @return array{0:string,1:int,2:int,3:?Quiz,4:?Question}
     */
    private function resolveQuotaContext(User $user, string $resourceType, ?int $quizId, ?int $questionId): array
    {
        $quiz = null;
        $question = null;

        if (in_array($resourceType, ['questions', 'answers', 'students'], true)) {
            abort_unless($quizId !== null, 422);
            $quiz = Quiz::findOrFail($quizId);
            $this->authorizeQuizOwnership($user, $quiz);
        }

        if ($resourceType === 'answers') {
            if ($questionId !== null) {
                $question = Question::findOrFail($questionId);
                abort_unless($quiz && $question->quiz_id === $quiz->id, 404);
            }
        }

        return match ($resourceType) {
            'quizzes' => [
                __('controllers.request_more_quizzes'),
                (int) $user->max_quizzes,
                $user->quizzes()->count(),
                null,
                null,
            ],
            'questions' => [
                __('controllers.request_more_questions'),
                (int) $user->max_questions_per_quiz,
                $quiz->questions()->count(),
                $quiz,
                null,
            ],
            'answers' => [
                __('controllers.request_more_answers'),
                (int) $user->max_answers_per_question,
                $question
                    ? $question->answers()->count()
                    : (int) ($quiz?->questions()->withCount('answers')->get()->max('answers_count') ?? 0),
                $quiz,
                $question,
            ],
            'students' => [
                __('controllers.request_more_students'),
                (int) $user->max_students_per_quiz,
                QuizStudent::where('quiz_id', $quiz->id)->count(),
                $quiz,
                null,
            ],
        };
    }

    /**
     * Ensure that quota requests can only reference quizzes owned by the current teacher.
     */
    private function authorizeQuizOwnership(User $user, Quiz $quiz): void
    {
        if ($quiz->creator_id !== $user->id) {
            abort(403);
        }
    }
}
