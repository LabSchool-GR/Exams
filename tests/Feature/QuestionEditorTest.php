<?php

/**
 * QuestionEditorTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\UploadedFile;

/**
 * Create a teacher-owned quiz that can be reused across editor and import scenarios.
 */
function makeQuizForQuestionEditor(): array
{
    $user = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Test Category',
    ]);

    $quiz = Quiz::create([
        'title' => 'Question Editor Quiz',
        'description' => 'Test quiz',
        'category_id' => $category->id,
        'creator_id' => $user->id,
        'quiz_code' => 'QZ123456',
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
        'questions_limit' => 10,
        'is_public' => false,
        'language' => 'el',
    ]);

    return [$user, $quiz];
}

it('stores a question together with its answers from the unified editor', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    $response = $this
        ->actingAs($user)
        ->post(route('quizzes.questions.store', $quiz), [
            'text' => 'What is Laravel?',
            'order' => 1,
            'answers' => [
                ['text' => 'A PHP framework', 'is_correct' => '1'],
                ['text' => 'A database engine', 'is_correct' => '0'],
                ['text' => 'A CSS library', 'is_correct' => '0'],
            ],
        ]);

    $response->assertRedirect(route('quizzes.questions.index', $quiz));

    $question = Question::where('quiz_id', $quiz->id)->first();

    expect($question)->not->toBeNull();
    expect($question->text)->toBe('What is Laravel?');
    expect($question->correct_answers_count)->toBe(1);
    expect($question->answers()->count())->toBe(3);
    expect($question->answers()->where('is_correct', true)->count())->toBe(1);
});

it('rejects unified question submissions without a correct answer', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    $response = $this
        ->actingAs($user)
        ->from(route('quizzes.questions.create', $quiz))
        ->post(route('quizzes.questions.store', $quiz), [
            'text' => 'Invalid question',
            'answers' => [
                ['text' => 'Option A', 'is_correct' => '0'],
                ['text' => 'Option B', 'is_correct' => '0'],
            ],
        ]);

    $response
        ->assertRedirect(route('quizzes.questions.create', $quiz))
        ->assertSessionHasErrors('answers');

    expect(Question::where('quiz_id', $quiz->id)->count())->toBe(0);
});

it('updates answers in place and preserves existing answer ids where possible', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    $question = $quiz->questions()->create([
        'text' => 'Original question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $firstAnswer = $question->answers()->create([
        'text' => 'Old correct',
        'is_correct' => true,
    ]);

    $secondAnswer = $question->answers()->create([
        'text' => 'Old wrong',
        'is_correct' => false,
    ]);

    // Preserving stable answer ids avoids breaking existing attempt and reporting references.
    $response = $this
        ->actingAs($user)
        ->put(route('quizzes.questions.update', [$quiz, $question]), [
            'text' => 'Updated question',
            'order' => 2,
            'answers' => [
                ['id' => $firstAnswer->id, 'text' => 'Updated correct', 'is_correct' => '1'],
                ['id' => $secondAnswer->id, 'text' => 'Updated wrong', 'is_correct' => '0'],
                ['text' => 'New wrong', 'is_correct' => '0'],
            ],
        ]);

    $response->assertRedirect(route('quizzes.questions.index', $quiz));

    $question->refresh();
    $answers = $question->answers()->orderBy('id')->get();

    expect($question->text)->toBe('Updated question');
    expect($question->order)->toBe(2);
    expect($question->correct_answers_count)->toBe(1);
    expect($answers)->toHaveCount(3);
    expect($answers->pluck('id')->all())->toContain($firstAnswer->id, $secondAnswer->id);
    expect(Answer::find($firstAnswer->id)?->text)->toBe('Updated correct');
    expect(Answer::find($secondAnswer->id)?->text)->toBe('Updated wrong');
});

it('imports questions together with answers from csv', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    $csv = <<<'CSV'
text,answer_1,answer_2,answer_3,correct_answers
What is Laravel?,A PHP framework,A database engine,A CSS library,1
Select the vowels,A,B,E,"1,3"
CSV;

    $response = $this
        ->actingAs($user)
        ->from(route('quizzes.questions.index', $quiz))
        ->post(route('quizzes.questions.import', $quiz), [
            'questions_csv' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
        ]);

    $response->assertRedirect(route('quizzes.questions.index', $quiz));

    $questions = $quiz->questions()->with('answers')->orderBy('id')->get();

    expect($questions)->toHaveCount(2);
    expect($questions[0]->text)->toBe('What is Laravel?');
    expect($questions[0]->correct_answers_count)->toBe(1);
    expect($questions[0]->answers)->toHaveCount(3);
    expect($questions[0]->answers->where('is_correct', true)->pluck('text')->values()->all())->toBe(['A PHP framework']);
    expect($questions[1]->text)->toBe('Select the vowels');
    expect($questions[1]->correct_answers_count)->toBe(2);
    expect($questions[1]->answers)->toHaveCount(3);
    expect($questions[1]->answers->where('is_correct', true)->pluck('text')->values()->all())->toBe(['A', 'E']);
});

it('rejects legacy text-only csv question imports', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    // This guards against the historical import bug that produced unanswerable questions.
    $csv = <<<'CSV'
text
Question without answers
CSV;

    $response = $this
        ->actingAs($user)
        ->from(route('quizzes.questions.index', $quiz))
        ->post(route('quizzes.questions.import', $quiz), [
            'questions_csv' => UploadedFile::fake()->createWithContent('questions.csv', $csv),
        ]);

    $response
        ->assertRedirect(route('quizzes.questions.index', $quiz))
        ->assertSessionHas('error', 'CSV must contain the headers: text, answer_1, answer_2, ..., correct_answers.');

    expect($quiz->questions()->count())->toBe(0);
});

it('redirects legacy answer management routes to the unified question editor', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    $question = $quiz->questions()->create([
        'text' => 'Question for redirect',
        'correct_answers_count' => 1,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('quizzes.questions.answers.index', [$quiz, $question]));

    $response->assertRedirect(route('quizzes.questions.edit', [$quiz, $question]));
});

it('locks question changes while recorded attempts exist and re-enables them after all attempts are deleted', function () {
    [$user, $quiz] = makeQuizForQuestionEditor();

    $question = $quiz->questions()->create([
        'text' => 'Locked question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $question->answers()->createMany([
        ['text' => 'Correct', 'is_correct' => true],
        ['text' => 'Wrong', 'is_correct' => false],
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1234',
        'student_name' => 'Participant',
        'score' => 0,
        'status' => QuizAttempt::STATUS_IN_PROGRESS,
        'max_attempts' => 1,
    ]);

    $this->actingAs($user)
        ->get(route('quizzes.questions.create', $quiz))
        ->assertRedirect(route('quizzes.questions.index', $quiz))
        ->assertSessionHas('error', __('controllers.quiz_content_locked'));

    $this->actingAs($user)
        ->from(route('quizzes.questions.index', $quiz))
        ->put(route('quizzes.questions.update', [$quiz, $question]), [
            'text' => 'Attempted locked update',
            'order' => 2,
            'answers' => [
                ['id' => $question->answers()->orderBy('id')->first()->id, 'text' => 'Updated correct', 'is_correct' => '1'],
                ['id' => $question->answers()->orderBy('id')->skip(1)->first()->id, 'text' => 'Updated wrong', 'is_correct' => '0'],
            ],
        ])
        ->assertRedirect(route('quizzes.questions.index', $quiz))
        ->assertSessionHas('error', __('controllers.quiz_content_locked'));

    $question->refresh();
    expect($question->text)->toBe('Locked question');

    $attempt->delete();

    $this->actingAs($user)
        ->put(route('quizzes.questions.update', [$quiz, $question]), [
            'text' => 'Unlocked update',
            'order' => 2,
            'answers' => [
                ['id' => $question->answers()->orderBy('id')->first()->id, 'text' => 'Updated correct', 'is_correct' => '1'],
                ['id' => $question->answers()->orderBy('id')->skip(1)->first()->id, 'text' => 'Updated wrong', 'is_correct' => '0'],
            ],
        ])
        ->assertRedirect(route('quizzes.questions.index', $quiz));

    expect($question->fresh()->text)->toBe('Unlocked update');
});
