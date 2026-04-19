<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Quiz;

trait AuthorizesQuizOwnership
{
    /**
     * Allow read-only access to platform example quizzes for authenticated users.
     */
    protected function authorizeQuizReadAccess(Quiz $quiz): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Not authenticated.');
        }

        if ($user->isAdmin() || $quiz->creator_id === $user->id || $quiz->isSystemExample()) {
            return;
        }

        abort(403, 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το quiz.');
    }

    /**
     * Restrict quiz-owned resources to the quiz creator and administrators.
     */
    protected function authorizeQuizAccess(Quiz $quiz): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Not authenticated.');
        }

        if (! $user->isAdmin() && $quiz->creator_id !== $user->id) {
            abort(403, 'Δεν έχετε δικαίωμα πρόσβασης σε αυτό το quiz.');
        }
    }
}
