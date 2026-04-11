<?php

/**
 * AnswerAuthorizationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

function makeAnswerRouteDataForAuthorization(): array
{
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Answer Auth Category ' . uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Answer Authorization',
        'description' => 'Security test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $question = $quiz->questions()->create([
        'text' => 'Answer authorization question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $answer = $question->answers()->create([
        'text' => 'Existing answer',
        'is_correct' => true,
    ]);

    return [$owner, $quiz, $question, $answer];
}

it('allows the quiz creator to access the legacy answers route and redirects to question edit', function () {
    [$owner, $quiz, $question] = makeAnswerRouteDataForAuthorization();

    $this->actingAs($owner)
        ->get(route('quizzes.questions.answers.index', [$quiz, $question]))
        ->assertRedirect(route('quizzes.questions.edit', [$quiz, $question]));
});

it('allows the admin to access the legacy answer edit route and redirects to question edit', function () {
    [, $quiz, $question, $answer] = makeAnswerRouteDataForAuthorization();

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('quizzes.questions.answers.edit', [$quiz, $question, $answer]))
        ->assertRedirect(route('quizzes.questions.edit', [$quiz, $question]));
});

it('returns 403 when another teacher tries to access the legacy answers route', function () {
    [, $quiz, $question] = makeAnswerRouteDataForAuthorization();

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($otherTeacher)
        ->get(route('quizzes.questions.answers.index', [$quiz, $question]))
        ->assertForbidden();
});

it('returns 404 when the answer does not belong to the question route segment', function () {
    [$owner, $quiz, $question] = makeAnswerRouteDataForAuthorization();
    [, , , $foreignAnswer] = makeAnswerRouteDataForAuthorization();

    $this->actingAs($owner)
        ->get(route('quizzes.questions.answers.edit', [$quiz, $question, $foreignAnswer]))
        ->assertNotFound();
});
