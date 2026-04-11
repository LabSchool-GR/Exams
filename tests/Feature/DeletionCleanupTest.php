<?php

/**
 * DeletionCleanupTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */


use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Build a fully linked quiz graph with stored files so deletion paths can be verified end to end.
 */
function makeOwnedQuizWithFiles(): array
{
    Storage::fake('public');

    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Deletion Category ' . uniqid(),
    ]);

    Storage::disk('public')->put('quizzes_images/quiz-delete-test.png', 'quiz-image');
    Storage::disk('public')->put('questions_images/question-delete-test.png', 'question-image');

    $quiz = Quiz::create([
        'title' => 'Deletion Quiz',
        'description' => 'Deletion cleanup test',
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
        'image' => 'quizzes_images/quiz-delete-test.png',
    ]);

    $question = $quiz->questions()->create([
        'text' => 'Cleanup question',
        'image' => 'questions_images/question-delete-test.png',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $answer = $question->answers()->create([
        'text' => 'Correct',
        'is_correct' => true,
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1234',
        'student_name' => 'Cleanup Student',
        'max_attempts' => 2,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 2,
        'score' => 100,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$question->id],
        'answer_order' => [$question->id => [$answer->id]],
        'skipped_question_ids' => [],
        'current_question_index' => 1,
    ]);

    $attemptAnswer = QuizAttemptAnswer::create([
        'attempt_id' => $attempt->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'is_correct' => true,
    ]);

    $displaySession = QuizDisplaySession::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'quiz_attempt_id' => $attempt->id,
        'status' => QuizDisplaySession::STATUS_SUBMITTED,
        'state_version' => 2,
        'controller_claimed_at' => now(),
        'controller_last_seen_at' => now(),
        'screen_last_seen_at' => now(),
        'submitted_at' => now(),
    ]);

    return [$owner, $quiz, $question, $student, $attempt, $attemptAnswer, $displaySession];
}

it('deletes quiz rows and stored files without leaving leftovers', function () {
    [$owner, $quiz, $question, $student, $attempt, $attemptAnswer, $displaySession] = makeOwnedQuizWithFiles();

    $this->actingAs($owner)
        ->delete(route('quizzes.destroy', $quiz))
        ->assertRedirect(route('quizzes.index'));

    expect(Quiz::find($quiz->id))->toBeNull();
    expect(Question::find($question->id))->toBeNull();
    expect(QuizStudent::find($student->id))->toBeNull();
    expect(QuizAttempt::find($attempt->id))->toBeNull();
    expect(QuizAttemptAnswer::find($attemptAnswer->id))->toBeNull();
    expect(QuizDisplaySession::find($displaySession->id))->toBeNull();

    Storage::disk('public')->assertMissing('quizzes_images/quiz-delete-test.png');
    Storage::disk('public')->assertMissing('questions_images/question-delete-test.png');
});

it('deletes second-screen sessions when a quiz attempt is deleted', function () {
    [$owner, $quiz, $question, $student, $attempt, $attemptAnswer, $displaySession] = makeOwnedQuizWithFiles();

    $this->actingAs($owner)
        ->delete(route('quizzes.quiz_attempts.destroy', [$quiz, $attempt]))
        ->assertRedirect(route('quizzes.quiz_attempts.index', $quiz));

    expect(QuizAttempt::find($attempt->id))->toBeNull();
    expect(QuizAttemptAnswer::find($attemptAnswer->id))->toBeNull();
    expect(QuizDisplaySession::find($displaySession->id))->toBeNull();

    expect(Quiz::find($quiz->id))->not->toBeNull();
    expect(QuizStudent::find($student->id))->not->toBeNull();
    expect(Question::find($question->id))->not->toBeNull();
});

it('deletes user-owned quiz assets and auth leftovers when the profile is deleted', function () {
    [$owner, $quiz, $question] = makeOwnedQuizWithFiles();

    DB::table('sessions')->insert([
        'id' => 'cleanup-session',
        'user_id' => $owner->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Pest',
        'payload' => 'payload',
        'last_activity' => now()->timestamp,
    ]);

    DB::table('password_reset_tokens')->insert([
        'email' => $owner->email,
        'token' => 'cleanup-token',
        'created_at' => now(),
    ]);

    $this->actingAs($owner)
        ->delete('/profile', [
            'password' => 'password',
        ])
        ->assertRedirect('/');

    expect(User::find($owner->id))->toBeNull();
    expect(Quiz::find($quiz->id))->toBeNull();
    expect(Question::find($question->id))->toBeNull();
    expect(DB::table('sessions')->where('id', 'cleanup-session')->exists())->toBeFalse();
    expect(DB::table('password_reset_tokens')->where('email', $owner->email)->exists())->toBeFalse();

    Storage::disk('public')->assertMissing('quizzes_images/quiz-delete-test.png');
    Storage::disk('public')->assertMissing('questions_images/question-delete-test.png');
});

it('deletes the registered student together with all linked and legacy attempts', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Student Cleanup Category ' . uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Student Cleanup Quiz',
        'description' => 'Full student delete test',
        'category_id' => $category->id,
        'creator_id' => $teacher->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 2,
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

    $question = $quiz->questions()->create([
        'text' => 'Student cleanup question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $answer = $question->answers()->create([
        'text' => 'Correct answer',
        'is_correct' => true,
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '5678',
        'student_name' => 'Student To Delete',
        'max_attempts' => 2,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $linkedAttempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 2,
        'score' => 100,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
        'question_order' => [$question->id],
        'answer_order' => [$question->id => [$answer->id]],
        'skipped_question_ids' => [],
        'current_question_index' => 1,
    ]);

    // Keep one pre-migration style attempt without the foreign key to verify cleanup still covers legacy rows.
    $legacyAttempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => null,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 2,
        'score' => 0,
        'status' => QuizAttempt::STATUS_IN_PROGRESS,
        'question_order' => [$question->id],
        'answer_order' => [$question->id => [$answer->id]],
        'skipped_question_ids' => [],
        'current_question_index' => 0,
    ]);

    $linkedAnswer = QuizAttemptAnswer::create([
        'attempt_id' => $linkedAttempt->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'is_correct' => true,
    ]);

    $legacyAnswer = QuizAttemptAnswer::create([
        'attempt_id' => $legacyAttempt->id,
        'question_id' => $question->id,
        'answer_id' => $answer->id,
        'is_correct' => true,
    ]);

    $displaySession = QuizDisplaySession::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'quiz_attempt_id' => $linkedAttempt->id,
        'status' => QuizDisplaySession::STATUS_ACTIVE,
        'state_version' => 3,
        'controller_claimed_at' => now(),
        'controller_last_seen_at' => now(),
        'screen_last_seen_at' => now(),
    ]);

    $this->actingAs($teacher)
        ->delete(route('quiz_attempts.destroy_student', [$quiz, $student]))
        ->assertRedirect(route('quiz_attempts.register_students', $quiz));

    expect(QuizStudent::find($student->id))->toBeNull();
    expect(QuizAttempt::find($linkedAttempt->id))->toBeNull();
    expect(QuizAttempt::find($legacyAttempt->id))->toBeNull();
    expect(QuizAttemptAnswer::find($linkedAnswer->id))->toBeNull();
    expect(QuizAttemptAnswer::find($legacyAnswer->id))->toBeNull();
    expect(QuizDisplaySession::find($displaySession->id))->toBeNull();
});
