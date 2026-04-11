<?php

/**
 * AnswerController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizOwnership;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnswerController extends Controller
{
    use AuthorizesQuizOwnership;

    private function redirectWhenQuizContentLocked(Quiz $quiz): RedirectResponse
    {
        return redirect()
            ->route('quizzes.questions.index', $quiz)
            ->with('error', __('controllers.quiz_content_locked'));
    }

    private function ensureQuizContentUnlocked(Quiz $quiz): ?RedirectResponse
    {
        if (!$quiz->hasLockedContent()) {
            return null;
        }

        return $this->redirectWhenQuizContentLocked($quiz);
    }

    /**
     * Redirect answer management to the unified question editor.
     */
    public function index(Quiz $quiz, Question $question): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()->route('quizzes.questions.edit', [$quiz, $question]);
    }

    /**
     * Redirect legacy answer create requests to the unified question editor.
     */
    public function create(Quiz $quiz, Question $question): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()->route('quizzes.questions.edit', [$quiz, $question]);
    }

    /**
     * Redirect legacy answer store requests to the unified question editor.
     */
    public function store(Request $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()
            ->route('quizzes.questions.edit', [$quiz, $question])
            ->with('error', __('quizzes_cards.answer_editor_moved'));
    }

    /**
     * Redirect legacy answer show requests to the unified question editor.
     */
    public function show(Quiz $quiz, Question $question, Answer $answer): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);
        $this->authorizeAnswer($quiz, $question, $answer);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()->route('quizzes.questions.edit', [$quiz, $question]);
    }

    /**
     * Redirect legacy answer edit requests to the unified question editor.
     */
    public function edit(Quiz $quiz, Question $question, Answer $answer): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);
        $this->authorizeAnswer($quiz, $question, $answer);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()->route('quizzes.questions.edit', [$quiz, $question]);
    }

    /**
     * Redirect legacy answer update requests to the unified question editor.
     */
    public function update(Request $request, Quiz $quiz, Question $question, Answer $answer): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);
        $this->authorizeAnswer($quiz, $question, $answer);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()
            ->route('quizzes.questions.edit', [$quiz, $question])
            ->with('error', __('quizzes_cards.answer_editor_moved'));
    }

    /**
     * Redirect legacy answer delete requests to the unified question editor.
     */
    public function destroy(Quiz $quiz, Question $question, Answer $answer): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);
        $this->authorizeAnswer($quiz, $question, $answer);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        return redirect()
            ->route('quizzes.questions.edit', [$quiz, $question])
            ->with('error', __('quizzes_cards.answer_editor_moved'));
    }

    /**
     * Guard against cross-quiz nested route mismatches.
     */
    private function authorizeQuestion(Quiz $quiz, Question $question): void
    {
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }
    }

    /**
     * Ensure the answer belongs to the specified quiz and question.
     */
    private function authorizeAnswer(Quiz $quiz, Question $question, Answer $answer): void
    {
        if ($answer->question_id !== $question->id || $question->quiz_id !== $quiz->id) {
            abort(404);
        }
    }
}
