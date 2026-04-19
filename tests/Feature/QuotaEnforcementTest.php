<?php

/**
 * QuotaEnforcementTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\Category;
use App\Models\Quiz;
use App\Models\User;

/**
 * Create a teacher with configurable quota ceilings for enforcement scenarios.
 */
function makeTeacherWithQuotaOverrides(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'teacher',
        'max_quizzes' => 1,
        'max_questions_per_quiz' => 30,
        'max_answers_per_question' => 4,
        'max_students_per_quiz' => 30,
    ], $overrides));
}

/**
 * Create a baseline quiz owned by the given teacher so quota checks can target a real resource.
 */
function makeQuotaQuiz(User $owner, array $overrides = []): Quiz
{
    $category = Category::create([
        'name' => 'Quota Category '.uniqid(),
    ]);

    return Quiz::create(array_merge([
        'title' => 'Quota Quiz',
        'description' => 'Quota test quiz',
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
    ], $overrides));
}

it('blocks quiz creation when the teacher has reached the quiz quota', function () {
    $teacher = makeTeacherWithQuotaOverrides([
        'max_quizzes' => 1,
    ]);

    $existingQuiz = makeQuotaQuiz($teacher);

    $this->actingAs($teacher)
        ->get(route('quizzes.create'))
        ->assertRedirect(route('quizzes.index'))
        ->assertSessionHas('error');

    $this->actingAs($teacher)
        ->post(route('quizzes.store'), [
            'title' => 'Blocked Quiz',
            'description' => 'Should not be created',
            'category_id' => $existingQuiz->category_id,
            'time_limit' => 10,
            'pass_percentage' => 50,
            'question_view' => 'default',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'))
        ->assertSessionHas('error');

    expect($teacher->quizzes()->count())->toBe(1);
});

it('blocks question creation when the teacher has reached the question quota for the quiz', function () {
    $teacher = makeTeacherWithQuotaOverrides([
        'max_questions_per_quiz' => 1,
    ]);

    $quiz = makeQuotaQuiz($teacher);
    $quiz->questions()->create([
        'text' => 'Existing question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $this->actingAs($teacher)
        ->get(route('quizzes.questions.create', $quiz))
        ->assertRedirect(route('quizzes.questions.index', $quiz))
        ->assertSessionHas('error');
});

it('blocks storing a question when the submitted answers exceed the answer quota', function () {
    $teacher = makeTeacherWithQuotaOverrides([
        'max_answers_per_question' => 2,
    ]);

    $quiz = makeQuotaQuiz($teacher);

    // Enforce the answer quota before persistence so partial question rows are never created.
    $this->actingAs($teacher)
        ->from(route('quizzes.questions.create', $quiz))
        ->post(route('quizzes.questions.store', $quiz), [
            'text' => 'Too many answers question',
            'answers' => [
                ['text' => 'Answer 1', 'is_correct' => '1'],
                ['text' => 'Answer 2', 'is_correct' => '0'],
                ['text' => 'Answer 3', 'is_correct' => '0'],
            ],
        ])
        ->assertRedirect(route('quizzes.questions.create', $quiz))
        ->assertSessionHasErrors('answers');
});

it('blocks student registration when the teacher has reached the student quota', function () {
    $teacher = makeTeacherWithQuotaOverrides([
        'max_students_per_quiz' => 1,
    ]);

    $quiz = makeQuotaQuiz($teacher);

    $this->actingAs($teacher)
        ->post(route('quiz_attempts.store_student', $quiz), [
            'student_name' => 'Student One',
            'student_code' => '1234',
            'max_attempts' => 1,
        ])
        ->assertRedirect(route('quiz_attempts.register_students', $quiz));

    $this->actingAs($teacher)
        ->post(route('quiz_attempts.store_student', $quiz), [
            'student_name' => 'Student Two',
            'student_code' => '5678',
            'max_attempts' => 1,
        ])
        ->assertRedirect(route('quiz_attempts.register_students', $quiz))
        ->assertSessionHas('error');
});
