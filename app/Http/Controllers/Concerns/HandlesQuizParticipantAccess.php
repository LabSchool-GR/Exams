<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizStudent;
use Illuminate\Http\RedirectResponse;

trait HandlesQuizParticipantAccess
{
    /**
     * Query attempts through the registered-student foreign key for all new data paths.
     */
    private function attemptsForStudent(QuizStudent $student)
    {
        return QuizAttempt::where('quiz_student_id', $student->id);
    }

    private function ensureStudentPinAccessIsAllowed(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->supportsStudentPinAccess()) {
            return null;
        }

        $this->resetQuizRuntimeState();

        return redirect()->route('quiz.join')
            ->with('error', __('join.student_pin_access_disabled'));
    }

    private function ensureStudentCodeScreenIsAllowed(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->supportsStudentPinAccess() || $quiz->allow_guest) {
            return null;
        }

        return $this->ensureStudentPinAccessIsAllowed($quiz);
    }

    private function ensureStudentPersonalLinkAccessIsAllowed(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->supportsStudentPersonalLinks()) {
            return null;
        }

        $this->resetQuizRuntimeState();

        return redirect()->route('quiz.join')
            ->with('error', __('join.personal_link_access_disabled'));
    }

    private function ensureQuizIsActiveForParticipantFlow(Quiz $quiz): ?RedirectResponse
    {
        if ($quiz->status === 'active') {
            return null;
        }

        $this->resetQuizRuntimeState();

        return redirect()->route('quiz.join')
            ->with('error', __('join.quiz_inactive'));
    }

    private function resolveParticipantView(Quiz $quiz, string $screen): string
    {
        $templateCode = (string) ($quiz->question_view ?: 'default');
        $candidates = [
            'quiz.templates.'.$templateCode.'.'.$screen,
        ];

        if ($templateCode !== 'default') {
            $candidates[] = 'quiz.templates.default.'.$screen;
        }

        foreach ($candidates as $viewName) {
            if (view()->exists($viewName)) {
                return $viewName;
            }
        }

        abort(500, 'No participant quiz template view is available.');
    }
}
