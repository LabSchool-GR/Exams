<?php

/**
 * QuotaRequestTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Mail\QuotaIncreaseRequestMail;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * Create a teacher with predictable quota limits for request-email scenarios.
 */
function makeQuotaRequestTeacher(array $overrides = []): User
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
 * Create a teacher-owned quiz that can be referenced from quota escalation emails.
 */
function makeQuotaRequestQuiz(User $owner, array $overrides = []): Quiz
{
    $category = Category::create([
        'name' => 'Quota Request Category '.uniqid(),
    ]);

    return Quiz::create(array_merge([
        'title' => 'Quota Request Quiz',
        'description' => 'Quota request quiz',
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

it('sends a quota request email to administrators', function () {
    Mail::fake();
    Cache::flush();

    $teacher = makeQuotaRequestTeacher();
    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($teacher)
        ->post(route('quota_requests.store'), [
            'resource_type' => 'quizzes',
        ])
        ->assertSessionHas('success');

    Mail::assertQueued(QuotaIncreaseRequestMail::class, function (QuotaIncreaseRequestMail $mail) use ($teacher) {
        return $mail->payload['resource_type'] === 'quizzes'
            && $mail->payload['user_email'] === $teacher->email
            && $mail->payload['user_profile_url'] === route('users.show', $teacher)
            && $mail->payload['users_url'] === route('users.index');
    });
});

it('includes direct quiz and question links when the request is about answers', function () {
    Mail::fake();
    Cache::flush();

    $teacher = makeQuotaRequestTeacher();
    User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $quiz = makeQuotaRequestQuiz($teacher);
    $question = $quiz->questions()->create([
        'text' => 'Question with answer quota issue',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $this->actingAs($teacher)
        ->post(route('quota_requests.store'), [
            'resource_type' => 'answers',
            'quiz_id' => $quiz->id,
            'question_id' => $question->id,
        ])
        ->assertSessionHas('success');

    Mail::assertQueued(QuotaIncreaseRequestMail::class, function (QuotaIncreaseRequestMail $mail) use ($quiz, $question) {
        return $mail->payload['quiz_edit_url'] === route('quizzes.edit', $quiz)
            && $mail->payload['question_edit_url'] === route('quizzes.questions.edit', [$quiz, $question]);
    });
});

it('uses quiz-level answer usage when an answers quota request has no question id yet', function () {
    Mail::fake();
    Cache::flush();

    $teacher = makeQuotaRequestTeacher();
    User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    $quiz = makeQuotaRequestQuiz($teacher);
    $question = $quiz->questions()->create([
        'text' => 'Question without explicit answer request target',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $question->answers()->createMany([
        ['text' => 'One', 'is_correct' => true],
        ['text' => 'Two', 'is_correct' => false],
        ['text' => 'Three', 'is_correct' => false],
    ]);

    $this->actingAs($teacher)
        ->post(route('quota_requests.store'), [
            'resource_type' => 'answers',
            'quiz_id' => $quiz->id,
        ])
        ->assertSessionHas('success');

    Mail::assertQueued(QuotaIncreaseRequestMail::class, function (QuotaIncreaseRequestMail $mail) use ($quiz) {
        return $mail->payload['resource_type'] === 'answers'
            && $mail->payload['quiz_edit_url'] === route('quizzes.edit', $quiz)
            && $mail->payload['question_edit_url'] === null
            && $mail->payload['current_usage'] === 3;
    });
});

it('throttles repeated quota requests for the same resource', function () {
    Mail::fake();
    Cache::flush();

    $teacher = makeQuotaRequestTeacher();
    User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    // Repeated requests for the same teacher and resource context should be throttled to avoid admin spam.
    $this->actingAs($teacher)
        ->post(route('quota_requests.store'), [
            'resource_type' => 'quizzes',
        ])
        ->assertSessionHas('success');

    $this->actingAs($teacher)
        ->post(route('quota_requests.store'), [
            'resource_type' => 'quizzes',
        ])
        ->assertSessionHas('error');
});

it('releases the quota throttle key when queue dispatch fails', function () {
    Cache::flush();

    $teacher = makeQuotaRequestTeacher();
    User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@example.com',
    ]);

    Mail::shouldReceive('to->queue')
        ->once()
        ->andThrow(new RuntimeException('Queue unavailable'));

    $response = $this->actingAs($teacher)
        ->from(route('dashboard'))
        ->post(route('quota_requests.store'), [
            'resource_type' => 'quizzes',
        ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('error', __('controllers.quota_request_send_failed'));

    $cacheKey = sprintf('quota_request:%d:%s:%s:%s', $teacher->id, 'quizzes', 'none', 'none');

    expect(Cache::has($cacheKey))->toBeFalse();
});
