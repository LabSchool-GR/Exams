<?php

use App\Mail\QuizSuccessNotificationMail;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizStudent;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function makePassNotificationQuiz(bool $notifyCreatorOnPass): array
{
    $owner = User::factory()->create([
        'role' => 'teacher',
        'email' => 'teacher'.uniqid().'@example.com',
    ]);

    $category = Category::create([
        'name' => 'Pass Notification Flow Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Pass Notification Quiz',
        'description' => 'Pass notification flow test',
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
        'notify_creator_on_pass' => $notifyCreatorOnPass,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '5555',
        'student_name' => 'Successful Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $question = $quiz->questions()->create([
        'text' => 'Pass notification question',
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

    return [$owner, $quiz, $student, $correctAnswer];
}

it('emails the quiz creator when a participant passes and notifications are enabled', function () {
    Mail::fake();

    [$owner, $quiz, $student, $correctAnswer] = makePassNotificationQuiz(true);

    $this->get($student->accessLinkUrl(now()->addMinutes(30)))
        ->assertRedirect(route('quiz.start'));

    $this->get(route('quiz.start'))->assertOk();

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $quizKey]))
        ->assertRedirect();

    $questionKey = array_key_first(session('question_route_map', []));

    $this->post(route('quiz.submit_final', ['quizKey' => $quizKey]), [
        'current_question_key' => $questionKey,
        'answer_id' => [$correctAnswer->id],
    ])->assertRedirect(route('quiz.end', ['quizKey' => $quizKey]));

    $this->get(route('quiz.end', ['quizKey' => $quizKey]))->assertOk();

    Mail::assertQueued(QuizSuccessNotificationMail::class, function (QuizSuccessNotificationMail $mail) use ($owner, $quiz) {
        return $mail->hasTo($owner->email)
            && $mail->studentName === 'Successful Student'
            && $mail->quizTitle === $quiz->title
            && $mail->score === 100.0;
    });
});

it('does not email the quiz creator when a participant passes and notifications are disabled', function () {
    Mail::fake();

    [, $quiz, $student, $correctAnswer] = makePassNotificationQuiz(false);

    $this->get($student->accessLinkUrl(now()->addMinutes(30)))
        ->assertRedirect(route('quiz.start'));

    $this->get(route('quiz.start'))->assertOk();

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $quizKey]))
        ->assertRedirect();

    $questionKey = array_key_first(session('question_route_map', []));

    $this->post(route('quiz.submit_final', ['quizKey' => $quizKey]), [
        'current_question_key' => $questionKey,
        'answer_id' => [$correctAnswer->id],
    ])->assertRedirect(route('quiz.end', ['quizKey' => $quizKey]));

    $this->get(route('quiz.end', ['quizKey' => $quizKey]))->assertOk();

    Mail::assertNothingQueued();
});
