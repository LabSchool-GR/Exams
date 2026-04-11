<?php

/**
 * QuestionAuthorizationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

function makeQuizWithQuestionForAuthorization(array $ownerOverrides = [], array $quizOverrides = []): array
{
    $owner = User::factory()->create(array_merge([
        'role' => 'teacher',
    ], $ownerOverrides));

    $category = Category::create([
        'name' => 'Authorization Category ' . uniqid(),
    ]);

    $quiz = Quiz::create(array_merge([
        'title' => 'Authorization Quiz',
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
    ], $quizOverrides));

    $question = $quiz->questions()->create([
        'text' => 'Authorization test question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    return [$owner, $quiz, $question];
}

it('allows the quiz creator to open the question edit screen', function () {
    [$owner, $quiz, $question] = makeQuizWithQuestionForAuthorization();

    $response = $this
        ->actingAs($owner)
        ->get(route('quizzes.questions.edit', [$quiz, $question]));

    $response->assertOk();
});

it('allows the admin to open another users question edit screen', function () {
    [, $quiz, $question] = makeQuizWithQuestionForAuthorization();

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this
        ->actingAs($admin)
        ->get(route('quizzes.questions.edit', [$quiz, $question]));

    $response->assertOk();
});

it('returns 403 when another teacher tries to open the question edit screen', function () {
    [, $quiz, $question] = makeQuizWithQuestionForAuthorization();

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $response = $this
        ->actingAs($otherTeacher)
        ->get(route('quizzes.questions.edit', [$quiz, $question]));

    $response->assertForbidden();
});

it('returns 404 when the question does not belong to the quiz route segment', function () {
    [$owner, $quiz] = makeQuizWithQuestionForAuthorization();
    [, , $foreignQuestion] = makeQuizWithQuestionForAuthorization();

    $response = $this
        ->actingAs($owner)
        ->get(route('quizzes.questions.edit', [$quiz, $foreignQuestion]));

    $response->assertNotFound();
});
