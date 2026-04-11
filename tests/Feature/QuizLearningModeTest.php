<?php

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizStudent;
use App\Models\User;

function makeLearningModeQuiz(array $overrides = []): Quiz
{
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Learning Mode Category ' . uniqid(),
    ]);

    return Quiz::create(array_merge([
        'title' => 'Learning Mode Quiz',
        'description' => 'Learning mode runtime test',
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
        'is_learning_mode' => true,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'en',
    ], $overrides));
}

it('runs learning mode for registered students without persisting attempts or scores', function () {
    $quiz = makeLearningModeQuiz();

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '4321',
        'student_name' => 'Learning Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $question = $quiz->questions()->create([
        'text' => 'Learning question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $correctAnswer = $question->answers()->create([
        'text' => 'Correct answer',
        'is_correct' => true,
    ]);

    $question->answers()->create([
        'text' => 'Wrong answer',
        'is_correct' => false,
    ]);

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $quiz->quiz_code,
    ])->assertRedirect(route('quiz.join_student'));

    $this->post(route('quiz.validate_student'), [
        'student_code' => $student->student_code,
    ])
        ->assertRedirect(route('quiz.start'))
        ->assertSessionHas('learning_mode_active', true);

    expect(QuizAttempt::query()->count())->toBe(0);

    $this->get(route('quiz.start'))->assertOk();

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $quizKey]))->assertRedirect();

    $questionKey = array_key_first(session('question_route_map', []));

    $this->post(route('quiz.submit_answer', ['quizKey' => $quizKey, 'questionKey' => $questionKey]), [
        'current_question_key' => $questionKey,
        'answer_id' => [$correctAnswer->id],
    ])->assertRedirect(route('quiz.question', ['quizKey' => $quizKey, 'questionKey' => $questionKey]));

    $this->get(route('quiz.question', ['quizKey' => $quizKey, 'questionKey' => $questionKey]))
        ->assertOk()
        ->assertSee(__('join.learning_mode_feedback_correct'))
        ->assertSee('Correct answer');

    $this->post(route('quiz.submit_final', ['quizKey' => $quizKey]))
        ->assertRedirect(route('quiz.end', ['quizKey' => $quizKey]));

    $this->get(route('quiz.end', ['quizKey' => $quizKey]))
        ->assertOk()
        ->assertSee(__('join.learning_mode_result_message'))
        ->assertDontSee('Score:')
        ->assertDontSee('Required:');

    expect(QuizAttempt::query()->count())->toBe(0);
});

it('keeps skip question active in learning mode', function () {
    $quiz = makeLearningModeQuiz([
        'allow_guest' => true,
    ]);

    $firstQuestion = $quiz->questions()->create([
        'text' => 'First learning question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $firstQuestion->answers()->create([
        'text' => 'First correct answer',
        'is_correct' => true,
    ]);

    $secondQuestion = $quiz->questions()->create([
        'text' => 'Second learning question',
        'correct_answers_count' => 1,
        'order' => 2,
    ]);

    $secondQuestion->answers()->create([
        'text' => 'Second correct answer',
        'is_correct' => true,
    ]);

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $quiz->quiz_code,
    ])->assertRedirect(route('quiz.join_student'));

    $this->post(route('quiz.validate_student'), [
        'student_code' => '0000',
    ])
        ->assertRedirect(route('quiz.start'))
        ->assertSessionHas('learning_mode_active', true);

    $this->get(route('quiz.start'))->assertOk();

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $quizKey]))->assertRedirect();

    $questionRouteMap = session('question_route_map', []);
    $firstQuestionKey = array_search($firstQuestion->id, $questionRouteMap, true);
    $secondQuestionKey = array_search($secondQuestion->id, $questionRouteMap, true);

    $this->post(route('quiz.skip_question', ['quizKey' => $quizKey, 'questionKey' => $firstQuestionKey]), [
        'current_question_key' => $firstQuestionKey,
    ])->assertRedirect(route('quiz.question', ['quizKey' => $quizKey, 'questionKey' => $secondQuestionKey]));

    expect(QuizAttempt::query()->count())->toBe(0);
});
