<?php

/**
 * QuizDisplaySessionTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use App\Models\User;
use Illuminate\Support\Str;

function makeSecondScreenQuiz(array $ownerOverrides = [], array $quizOverrides = []): array
{
    $owner = User::factory()->create(array_merge([
        'role' => 'admin',
    ], $ownerOverrides));

    $category = Category::create([
        'name' => 'Second Screen Category ' . uniqid(),
    ]);

    $quiz = Quiz::create(array_merge([
        'title' => 'Second Screen Quiz',
        'description' => 'Second screen test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => true,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => true,
        'is_learning_mode' => false,
        'is_certificate_verification_enabled' => false,
        'is_second_screen_enabled' => true,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'en',
    ], $quizOverrides));

    $firstQuestion = $quiz->questions()->create([
        'text' => 'First display question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $firstCorrect = $firstQuestion->answers()->create([
        'text' => 'Correct first answer',
        'is_correct' => true,
    ]);

    $firstQuestion->answers()->create([
        'text' => 'Wrong first answer',
        'is_correct' => false,
    ]);

    $secondQuestion = $quiz->questions()->create([
        'text' => 'Second display question',
        'correct_answers_count' => 1,
        'order' => 2,
    ]);

    $secondCorrect = $secondQuestion->answers()->create([
        'text' => 'Correct second answer',
        'is_correct' => true,
    ]);

    $secondQuestion->answers()->create([
        'text' => 'Wrong second answer',
        'is_correct' => false,
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1234',
        'student_name' => 'Screen Student',
        'max_attempts' => 2,
        'is_anonymous' => false,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    return [$owner, $quiz, $student, $firstQuestion, $firstCorrect, $secondQuestion, $secondCorrect];
}

it('stores second screen mode only when created by an admin', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $admin = User::factory()->create(['role' => 'admin']);
    $category = Category::create(['name' => 'Second Screen Toggle Category ' . uniqid()]);

    $this->actingAs($teacher)
        ->post(route('quizzes.store'), [
            'title' => 'Teacher Toggle Quiz',
            'description' => 'Teacher should not enable second screen mode',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_second_screen_enabled' => '1',
            'status' => 'active',
            'language' => 'en',
        ])
        ->assertRedirect(route('quizzes.index'));

    $teacherQuiz = Quiz::query()->where('creator_id', $teacher->id)->latest('id')->first();

    expect($teacherQuiz)->not->toBeNull()
        ->and($teacherQuiz->usesSecondScreenMode())->toBeFalse();

    $this->actingAs($admin)
        ->post(route('quizzes.store'), [
            'title' => 'Admin Toggle Quiz',
            'description' => 'Admin enables second screen mode',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_second_screen_enabled' => '1',
            'status' => 'active',
            'language' => 'en',
        ])
        ->assertRedirect(route('quizzes.index'));

    $adminQuiz = Quiz::query()->where('creator_id', $admin->id)->latest('id')->first();

    expect($adminQuiz)->not->toBeNull()
        ->and($adminQuiz->usesSecondScreenMode())->toBeTrue();
});

it('launches and reuses a second screen session for the same participant', function () {
    [$owner, $quiz, $student] = makeSecondScreenQuiz();

    $firstResponse = $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]));

    $firstLocation = $firstResponse->headers->get('Location');

    $firstResponse->assertStatus(302);

    expect($firstLocation)->toContain('/display/sessions/');

    $createdSessions = QuizDisplaySession::query()
        ->where('quiz_id', $quiz->id)
        ->where('quiz_student_id', $student->id)
        ->get();

    expect($createdSessions)->toHaveCount(1);

    $secondResponse = $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]));

    $secondLocation = $secondResponse->headers->get('Location');

    expect($secondLocation)->toContain('/display/sessions/')
        ->and(QuizDisplaySession::query()
            ->where('quiz_id', $quiz->id)
            ->where('quiz_student_id', $student->id)
            ->count())->toBe(1);
});

it('allows only the first mobile session to claim the controller', function () {
    [$owner, $quiz, $student] = makeSecondScreenQuiz();

    $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]))
        ->assertStatus(302);

    $displaySession = QuizDisplaySession::query()->where('quiz_student_id', $student->id)->latest('id')->firstOrFail();
    $pairUrl = $displaySession->pairUrl();
    $sessionCookie = config('session.cookie');

    $this->withCookie($sessionCookie, Str::random(40))
        ->get($pairUrl)
        ->assertRedirect(route('quiz_display.controller', $displaySession));

    $displaySession->refresh();

    expect($displaySession->isControllerClaimed())->toBeTrue();

    $this->withCookie($sessionCookie, Str::random(40))
        ->get($pairUrl)
        ->assertOk()
        ->assertSee(__('display.session_already_claimed'));
});

it('synchronizes answer selection navigation and submission for the claimed controller', function () {
    [$owner, $quiz, $student, $firstQuestion, $firstCorrect, $secondQuestion, $secondCorrect] = makeSecondScreenQuiz();

    $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]))
        ->assertStatus(302);

    $displaySession = QuizDisplaySession::query()->where('quiz_student_id', $student->id)->latest('id')->firstOrFail();
    $pairUrl = $displaySession->pairUrl();
    $sessionCookie = config('session.cookie');
    $controllerCookie = Str::random(40);

    $this->withCookie($sessionCookie, $controllerCookie)
        ->get($pairUrl)
        ->assertRedirect(route('quiz_display.controller', $displaySession));

    $stateResponse = $this->withCookie($sessionCookie, $controllerCookie)
        ->get(route('quiz_display.controller_state', $displaySession))
        ->assertOk()
        ->assertJsonPath('question.id', $firstQuestion->id)
        ->assertJsonPath('actions.can_next', false);

    $stateVersion = $stateResponse->json('session.state_version');

    $this->get($displaySession->screenStateUrl())
        ->assertOk()
        ->assertJsonPath('session.id', $displaySession->id);

    $answerResponse = $this->withCookie($sessionCookie, $controllerCookie)
        ->post(route('quiz_display.answer', $displaySession), [
            'answer_ids' => [$firstCorrect->id],
        ])
        ->assertOk()
        ->assertJsonPath('question.selected_answer_ids.0', $firstCorrect->id)
        ->assertJsonPath('actions.can_next', true);

    expect($answerResponse->json('session.state_version'))->toBeGreaterThan($stateVersion);

    $this->withCookie($sessionCookie, $controllerCookie)
        ->post(route('quiz_display.navigate', $displaySession), [
            'direction' => 'next',
        ])
        ->assertOk()
        ->assertJsonPath('question.id', $secondQuestion->id)
        ->assertJsonPath('progress.current', 2);

    $this->withCookie($sessionCookie, $controllerCookie)
        ->post(route('quiz_display.answer', $displaySession), [
            'answer_ids' => [$secondCorrect->id],
        ])
        ->assertOk()
        ->assertJsonPath('actions.can_submit', true);

    $submitResponse = $this->withCookie($sessionCookie, $controllerCookie)
        ->post(route('quiz_display.submit', $displaySession))
        ->assertOk()
        ->assertJsonPath('session.status', QuizDisplaySession::STATUS_SUBMITTED)
        ->assertJsonPath('attempt.status', QuizAttempt::STATUS_SUBMITTED);

    expect((float) $submitResponse->json('attempt.score'))->toBe(100.0);
});

it('keeps the screen state endpoint available long enough to render an expired session', function () {
    [$owner, $quiz, $student] = makeSecondScreenQuiz([], [
        'has_timer' => true,
        'time_limit' => 60,
    ]);

    $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]))
        ->assertStatus(302);

    $displaySession = QuizDisplaySession::query()
        ->where('quiz_student_id', $student->id)
        ->latest('id')
        ->firstOrFail();

    $displaySession->attempt()->update([
        'started_at' => now()->subMinutes(2),
        'expires_at' => now()->subSecond(),
        'status' => QuizAttempt::STATUS_IN_PROGRESS,
        'submitted_at' => null,
        'finalized_at' => null,
    ]);

    $displaySession->update([
        'status' => QuizDisplaySession::STATUS_ACTIVE,
    ]);

    $this->get($displaySession->screenStateUrl())
        ->assertOk()
        ->assertJsonPath('session.status', QuizDisplaySession::STATUS_EXPIRED)
        ->assertJsonPath('attempt.status', QuizAttempt::STATUS_EXPIRED);
});

it('returns no content when second-screen state version has not changed', function () {
    [$owner, $quiz, $student] = makeSecondScreenQuiz();

    $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]))
        ->assertStatus(302);

    $displaySession = QuizDisplaySession::query()
        ->where('quiz_student_id', $student->id)
        ->latest('id')
        ->firstOrFail();

    $pairUrl = $displaySession->pairUrl();
    $sessionCookie = config('session.cookie');
    $controllerCookie = Str::random(40);

    $this->withCookie($sessionCookie, $controllerCookie)
        ->get($pairUrl)
        ->assertRedirect(route('quiz_display.controller', $displaySession));

    $controllerState = $this->withCookie($sessionCookie, $controllerCookie)
        ->get(route('quiz_display.controller_state', $displaySession))
        ->assertOk();

    $controllerVersion = (int) $controllerState->json('session.state_version');

    $this->withCookie($sessionCookie, $controllerCookie)
        ->get(route('quiz_display.controller_state', $displaySession, false) . '?since=' . $controllerVersion)
        ->assertNoContent();
});

it('allows an admin to terminate tv mode and exposes a signed result pdf url', function () {
    [$owner, $quiz, $student] = makeSecondScreenQuiz();

    $this->actingAs($owner)
        ->post(route('quiz_display.launch', [$quiz, $student]))
        ->assertStatus(302);

    $displaySession = QuizDisplaySession::query()
        ->where('quiz_student_id', $student->id)
        ->latest('id')
        ->firstOrFail();

    $this->actingAs($owner)
        ->post(route('quiz_display.terminate', [$quiz, $displaySession]))
        ->assertRedirect(route('quiz_attempts.register_students', $quiz))
        ->assertSessionHas('success', __('display.terminated_success'));

    $displaySession->refresh();
    $attempt = $displaySession->attempt()->firstOrFail();

    expect($displaySession->status)->toBe(QuizDisplaySession::STATUS_SUBMITTED)
        ->and($attempt->status)->toBe(QuizAttempt::STATUS_SUBMITTED)
        ->and($attempt->finish_reason)->toBe(QuizAttempt::FINISH_REASON_ADMIN_TERMINATED);

    $screenState = $this->get($displaySession->screenStateUrl())
        ->assertOk()
        ->assertJsonPath('session.status', QuizDisplaySession::STATUS_SUBMITTED)
        ->assertJsonPath('attempt.status', QuizAttempt::STATUS_SUBMITTED)
        ->assertJsonPath('attempt.finish_reason', QuizAttempt::FINISH_REASON_ADMIN_TERMINATED)
        ->assertJsonPath('result.reason_label', __('display.result_reason_admin_terminated'));

    expect((string) $screenState->json('result.pdf_url'))->toContain('/pdf/signed');
});
