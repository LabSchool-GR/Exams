<?php

/**
 * QuizAttemptAuthorizationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Http\Controllers\QuizAttemptController;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizStudent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Create a minimal submitted attempt that can be reused across access-control and export scenarios.
 */
function makeQuizAttemptForAuthorization(): array
{
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Attempt Auth Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Attempt Authorization',
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
        'text' => 'Attempt authorization question',
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

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1234',
        'student_name' => 'Student Example',
        'max_attempts' => 1,
        'score' => 100,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$question->id],
        'answer_order' => [$question->id => [$correctAnswer->id]],
        'skipped_question_ids' => [],
        'current_question_index' => 1,
    ]);

    return [$owner, $quiz, $attempt];
}

it('allows the quiz creator to view quiz attempts index', function () {
    [$owner, $quiz] = makeQuizAttemptForAuthorization();

    $this->actingAs($owner)
        ->get(route('quizzes.quiz_attempts.index', $quiz))
        ->assertOk();
});

it('allows the admin to view another users quiz attempts index', function () {
    [, $quiz] = makeQuizAttemptForAuthorization();

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('quizzes.quiz_attempts.index', $quiz))
        ->assertOk();
});

it('returns 403 when another teacher tries to view quiz attempts index', function () {
    [, $quiz] = makeQuizAttemptForAuthorization();

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($otherTeacher)
        ->get(route('quizzes.quiz_attempts.index', $quiz))
        ->assertForbidden();
});

it('lists registered students by student code to avoid decrypt-and-sort in memory', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Register Students Order Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Register Students Order Quiz',
        'description' => 'Ordering test quiz',
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

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '3003',
        'student_name' => 'Zeta Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1001',
        'student_name' => 'Beta Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2002',
        'student_name' => 'Alpha Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    // Ordering by student code keeps the listing query database-native even when names are encrypted.
    $response = $this->actingAs($owner)
        ->get(route('quiz_attempts.register_students', $quiz))
        ->assertOk();

    $content = $response->getContent();

    expect(strpos($content, 'Beta Student'))->toBeLessThan(strpos($content, 'Alpha Student'))
        ->and(strpos($content, 'Alpha Student'))->toBeLessThan(strpos($content, 'Zeta Student'));
});

it('generates anonymous participant slots with unique codes and independent attempt limits', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Anonymous Slots Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Anonymous Slots Quiz',
        'description' => 'Anonymous slots test quiz',
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
        'is_anonymous_bulk_mode' => true,
        'language' => 'el',
    ]);

    $this->actingAs($owner)
        ->post(route('quiz_attempts.store_anonymous_students', $quiz), [
            'anonymous_slots_count' => 3,
            'anonymous_max_attempts' => 2,
        ])
        ->assertRedirect(route('quiz_attempts.register_students', $quiz));

    $students = QuizStudent::query()
        ->where('quiz_id', $quiz->id)
        ->orderBy('student_code')
        ->get();

    expect($students)->toHaveCount(3)
        ->and($students->pluck('student_name')->unique()->all())->toBe([__('controllers.anonymous_student_name')])
        ->and($students->pluck('max_attempts')->unique()->all())->toBe([2])
        ->and($students->pluck('student_code')->unique()->count())->toBe(3)
        ->and($students->every(fn (QuizStudent $student) => preg_match('/^\d{4}$/', $student->student_code) === 1 && $student->student_code !== '0000'))->toBeTrue();
});

it('limits manual student registrations to five attempts even when resume is disabled', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Manual Attempts Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Manual Attempts Quiz',
        'description' => 'Attempt limit test quiz',
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

    $this->actingAs($owner)
        ->from(route('quiz_attempts.register_students', $quiz))
        ->post(route('quiz_attempts.store_student', $quiz), [
            'student_name' => 'Attempt Limit Student',
            'student_code' => '5151',
            'max_attempts' => 6,
        ])
        ->assertRedirect(route('quiz_attempts.register_students', $quiz))
        ->assertSessionHasErrors(['max_attempts']);

    expect(QuizStudent::query()->where('quiz_id', $quiz->id)->count())->toBe(0);
});

it('rejects imported students that use the reserved guest code', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Reserved Guest Code Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Reserved Guest Code Quiz',
        'description' => 'Reserved code import test quiz',
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
        'allow_resume' => true,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $csv = <<<'CSV'
student_name,student_code,max_attempts
Guest Alias,0000,1
CSV;

    $this->actingAs($owner)
        ->from(route('quiz_attempts.register_students', $quiz))
        ->post(route('quiz_attempts.import_students', $quiz), [
            'students_csv' => UploadedFile::fake()->createWithContent('students.csv', $csv),
        ])
        ->assertRedirect(route('quiz_attempts.register_students', $quiz))
        ->assertSessionHas('error', 'Line 2: Student code 0000 is reserved for guest access.');

    expect(QuizStudent::query()->where('quiz_id', $quiz->id)->count())->toBe(0);
});

it('allows the quiz creator to download an attempt pdf', function () {
    [$owner, $quiz, $attempt] = makeQuizAttemptForAuthorization();

    $this->actingAs($owner)
        ->get(route('quiz_attempts.download_pdf', [$quiz, $attempt]))
        ->assertOk();
});

it('allows the quiz creator to download a student info pdf', function () {
    [$owner, $quiz] = makeQuizAttemptForAuthorization();

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1111',
        'student_name' => 'Student Info Example',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $this->actingAs($owner)
        ->get(route('quiz_attempts.student_info_pdf', ['quiz' => $quiz, 'student_code' => '1111']))
        ->assertOk();
});

it('returns 403 when another teacher tries to download an attempt pdf', function () {
    [, $quiz, $attempt] = makeQuizAttemptForAuthorization();

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($otherTeacher)
        ->get(route('quiz_attempts.download_pdf', [$quiz, $attempt]))
        ->assertForbidden();
});

it('exports question stats with localized headings', function () {
    [$owner, $quiz] = makeQuizAttemptForAuthorization();

    // Freeze time so the generated export filename becomes deterministic in the assertion below.
    Carbon::setTestNow('2026-03-24 12:00:00');
    Excel::fake();

    try {
        $this->actingAs($owner)
            ->get(route('quiz_attempts.question_stats_export', $quiz))
            ->assertOk();

        Excel::assertDownloaded('quiz_stats_20260324_120000.xlsx', function ($export) {
            return method_exists($export, 'headings')
                && $export->headings() === [
                    '#',
                    __('quizzes.question_text'),
                    __('quizzes.correct_stats'),
                    __('quizzes.incorrect_stats'),
                    __('quizzes.unanswered_stats'),
                    __('quizzes.score_stats'),
                ];
        });
    } finally {
        Carbon::setTestNow();
    }
});

it('returns 404 when the attempt does not belong to the quiz route segment', function () {
    [$owner, $quiz] = makeQuizAttemptForAuthorization();
    [, , $foreignAttempt] = makeQuizAttemptForAuthorization();

    $this->actingAs($owner)
        ->get(route('quiz_attempts.download_pdf', [$quiz, $foreignAttempt]))
        ->assertNotFound();
});

it('shows correct wrong and unanswered counts for question stats', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Stats Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Question Stats Quiz',
        'description' => 'Stats test quiz',
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
        'text' => 'Stats question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $correctAnswer = $question->answers()->create([
        'text' => 'Correct',
        'is_correct' => true,
    ]);

    $wrongAnswer = $question->answers()->create([
        'text' => 'Wrong',
        'is_correct' => false,
    ]);

    $correctAttempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1001',
        'student_name' => 'Correct Student',
        'max_attempts' => 1,
        'score' => 100,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$question->id],
        'current_question_index' => 1,
    ]);

    $wrongAttempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1002',
        'student_name' => 'Wrong Student',
        'max_attempts' => 1,
        'score' => 0,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$question->id],
        'current_question_index' => 1,
    ]);

    $unansweredAttempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1003',
        'student_name' => 'Unanswered Student',
        'max_attempts' => 1,
        'score' => 0,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$question->id],
        'current_question_index' => 1,
    ]);

    $correctAttempt->answers()->create([
        'question_id' => $question->id,
        'answer_id' => $correctAnswer->id,
        'is_correct' => true,
    ]);

    $wrongAttempt->answers()->create([
        'question_id' => $question->id,
        'answer_id' => $wrongAnswer->id,
        'is_correct' => false,
    ]);

    $this->actingAs($owner)
        ->get(route('quiz_attempts.question_stats', $quiz))
        ->assertOk()
        ->assertViewHas('stats', function (array $stats) {
            return count($stats) === 1
                && $stats[0]['correct'] === 1
                && $stats[0]['wrong'] === 1
                && $stats[0]['unanswered'] === 1
                && $stats[0]['success_rate'] === 50.0;
        });
});

it('counts unanswered only for attempts that actually included the question in random quizzes', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Random Stats Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Random Stats Quiz',
        'description' => 'Random stats test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => true,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => 1,
        'is_public' => false,
        'language' => 'el',
    ]);

    $questionOne = $quiz->questions()->create([
        'text' => 'Question One',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $questionTwo = $quiz->questions()->create([
        'text' => 'Question Two',
        'correct_answers_count' => 1,
        'order' => 2,
    ]);

    $correctAnswerOne = $questionOne->answers()->create([
        'text' => 'Correct One',
        'is_correct' => true,
    ]);

    $questionOne->answers()->create([
        'text' => 'Wrong One',
        'is_correct' => false,
    ]);

    $questionTwo->answers()->create([
        'text' => 'Correct Two',
        'is_correct' => true,
    ]);

    $attemptSawOneAndAnswered = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2001',
        'student_name' => 'Saw One',
        'max_attempts' => 1,
        'score' => 100,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$questionOne->id],
        'current_question_index' => 1,
    ]);

    $attemptSawTwoOnly = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2002',
        'student_name' => 'Saw Two Only',
        'max_attempts' => 1,
        'score' => 0,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$questionTwo->id],
        'current_question_index' => 1,
    ]);

    $attemptSawOneAndSkipped = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2003',
        'student_name' => 'Skipped One',
        'max_attempts' => 1,
        'score' => 0,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$questionOne->id],
        'current_question_index' => 1,
    ]);

    $attemptSawOneAndAnswered->answers()->create([
        'question_id' => $questionOne->id,
        'answer_id' => $correctAnswerOne->id,
        'is_correct' => true,
    ]);

    // Randomized quizzes should only count unanswered for questions that were actually shown in that attempt.
    $this->actingAs($owner)
        ->get(route('quiz_attempts.question_stats', $quiz))
        ->assertOk()
        ->assertViewHas('stats', function (array $stats) use ($questionOne, $questionTwo) {
            $byQuestion = collect($stats)->keyBy('question');

            return $byQuestion->has($questionOne->text)
                && $byQuestion->has($questionTwo->text)
                && $byQuestion[$questionOne->text]['correct'] === 1
                && $byQuestion[$questionOne->text]['wrong'] === 0
                && $byQuestion[$questionOne->text]['unanswered'] === 1
                && $byQuestion[$questionTwo->text]['correct'] === 0
                && $byQuestion[$questionTwo->text]['wrong'] === 0
                && $byQuestion[$questionTwo->text]['unanswered'] === 1;
        });
});

it('allows public certificate verification through a signed url with minimized details', function () {
    [, $quiz, $attempt] = makeQuizAttemptForAuthorization();
    $quiz->update(['is_certificate_verification_enabled' => true]);

    $signedUrl = URL::temporarySignedRoute('quiz_attempts.verify', now()->addMinutes(30), ['attempt' => $attempt->id]);

    $this->get($signedUrl)
        ->assertOk()
        ->assertSee((string) $attempt->id)
        ->assertSee($quiz->title)
        ->assertSee(__('verify.public_minimized_notice'))
        ->assertDontSee($attempt->student_name);
});

it('blocks public certificate verification when the quiz disables it', function () {
    [, , $attempt] = makeQuizAttemptForAuthorization();

    $signedUrl = URL::temporarySignedRoute('quiz_attempts.verify', now()->addMinutes(30), ['attempt' => $attempt->id]);

    $this->get($signedUrl)
        ->assertNotFound();
});
it('hides certificate verification details when no signature is provided', function () {
    [$owner, , $attempt] = makeQuizAttemptForAuthorization();

    $this->get(route('quiz_attempts.verify', ['attempt' => $attempt->id]))
        ->assertNotFound();

    $this->actingAs($owner)
        ->get(route('quiz_attempts.verify', ['attempt' => $attempt->id]))
        ->assertOk()
        ->assertSee($attempt->student_name);
});

it('uses the persisted question order for result exports when randomized attempts include unanswered questions', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Random Export Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Random Export Quiz',
        'description' => 'Random export test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => true,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => 2,
        'is_public' => false,
        'language' => 'el',
    ]);

    $questionOne = $quiz->questions()->create([
        'text' => 'Export Question One',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $questionTwo = $quiz->questions()->create([
        'text' => 'Export Question Two',
        'correct_answers_count' => 1,
        'order' => 2,
    ]);

    $questionThree = $quiz->questions()->create([
        'text' => 'Export Question Three',
        'correct_answers_count' => 1,
        'order' => 3,
    ]);

    $answerTwo = $questionTwo->answers()->create([
        'text' => 'Correct Two',
        'is_correct' => true,
    ]);

    $questionOne->answers()->create([
        'text' => 'Correct One',
        'is_correct' => true,
    ]);

    $questionThree->answers()->create([
        'text' => 'Correct Three',
        'is_correct' => true,
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '3111',
        'student_name' => 'Export Student',
        'max_attempts' => 1,
        'score' => 50,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$questionTwo->id, $questionOne->id],
        'current_question_index' => 2,
    ]);

    $attempt->answers()->create([
        'question_id' => $questionTwo->id,
        'answer_id' => $answerTwo->id,
        'is_correct' => true,
    ]);

    // Result exports must honor the stored attempt order instead of falling back to database ordering.
    $controller = app(QuizAttemptController::class);
    $method = new ReflectionMethod($controller, 'determineQuestionIds');
    $method->setAccessible(true);
    $questionIds = $method->invoke($controller, $quiz->fresh('questions'), $attempt->fresh('answers'));

    expect($questionIds)->toBe([$questionTwo->id, $questionOne->id]);
});

it('abandons an existing attempt before creating a fresh one when resume is disabled', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Resume Disabled Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Resume Disabled Quiz',
        'description' => 'Resume flow test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 2,
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
        'text' => 'Resume Question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $answer = $question->answers()->create([
        'text' => 'Correct answer',
        'is_correct' => true,
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '4321',
        'student_name' => 'Resume Student',
        'max_attempts' => 2,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $existingAttempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 2,
        'score' => 0,
        'status' => QuizAttempt::STATUS_IN_PROGRESS,
        'question_order' => [$question->id],
        'current_question_index' => 0,
    ]);

    $existingAttempt->answers()->create([
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'is_correct' => true,
    ]);

    $this->get($student->accessLinkUrl(now()->addMinutes(30)))
        ->assertRedirect(route('quiz.start'));

    $existingAttempt->refresh();
    $newAttempt = QuizAttempt::query()
        ->where('quiz_id', $quiz->id)
        ->where('id', '!=', $existingAttempt->id)
        ->latest('id')
        ->first();

    expect($existingAttempt->status)->toBe(QuizAttempt::STATUS_ABANDONED)
        ->and($existingAttempt->finish_reason)->toBe(QuizAttempt::FINISH_REASON_ABANDONED)
        ->and($existingAttempt->submitted_at)->not->toBeNull()
        ->and($existingAttempt->finalized_at)->not->toBeNull()
        ->and($existingAttempt->answers()->count())->toBe(0)
        ->and($newAttempt)->not->toBeNull()
        ->and($newAttempt->status)->toBe(QuizAttempt::STATUS_IN_PROGRESS)
        ->and($newAttempt->quiz_student_id)->toBe($student->id);
});

it('shows a retry link for failed private student attempts when attempts remain', function () {
    Carbon::setTestNow('2026-03-25 12:00:00');

    try {
        $owner = User::factory()->create([
            'role' => 'teacher',
        ]);

        $category = Category::create([
            'name' => 'Private Retry Category '.uniqid(),
        ]);

        $quiz = Quiz::create([
            'title' => 'Private Retry Quiz',
            'description' => 'Retry link test quiz',
            'category_id' => $category->id,
            'creator_id' => $owner->id,
            'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
            'max_attempts' => 2,
            'time_limit' => 600,
            'is_random_order' => false,
            'is_random_answers_order' => false,
            'show_answer_numbering' => false,
            'allow_guest' => false,
            'has_timer' => false,
            'allow_resume' => true,
            'pass_percentage' => 80,
            'question_view' => 'default',
            'status' => 'active',
            'questions_limit' => null,
            'is_public' => false,
            'language' => 'el',
        ]);

        $student = QuizStudent::create([
            'quiz_id' => $quiz->id,
            'student_code' => '7777',
            'student_name' => 'Retry Student',
            'max_attempts' => 2,
            'access_token_hash' => QuizStudent::generateLinkTokenHash(),
        ]);

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'quiz_student_id' => $student->id,
            'student_code' => $student->student_code,
            'student_name' => $student->student_name,
            'max_attempts' => 2,
            'score' => 50,
            'status' => QuizAttempt::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'finalized_at' => now(),
            'question_order' => [],
            'current_question_index' => 0,
        ]);

        $html = view('quiz.templates.default.result', [
            'quiz' => $quiz,
            'attempt' => $attempt,
            'totalQuestions' => 2,
            'correctCount' => 1,
            'scorePercentage' => 50.0,
            'remainingAttempts' => 1,
        ])->render();

        expect($html)->toContain(e($student->accessLinkUrl(now()->addMinutes((int) config('security.signed_urls.student_link_ttl_minutes', 10080)))));
    } finally {
        Carbon::setTestNow();
    }
});
