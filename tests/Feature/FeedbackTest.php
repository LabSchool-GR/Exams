<?php

use App\Mail\AdminFeedbackAlert;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('feedback is delivered only to admins and includes sender identity in the email body', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@sch.gr',
    ]);

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
        'email' => 'other-teacher@sch.gr',
    ]);

    $sender = User::factory()->create([
        'role' => 'teacher',
        'name' => 'Feedback Sender',
        'email' => 'sender@sch.gr',
    ]);

    $response = $this->actingAs($sender)->post(route('feedback.store'), [
        'title' => 'Feedback Title',
        'message' => 'Feedback body for admins.',
    ]);

    $response->assertRedirect(route('dashboard'));
    $response->assertSessionHas('success', __('dashboard.feedback_sent'));

    Mail::assertQueued(AdminFeedbackAlert::class, function (AdminFeedbackAlert $mail) use ($admin) {
        $rendered = $mail->render();

        return $mail->hasTo($admin->email)
            && str_contains($rendered, 'Feedback Title')
            && str_contains($rendered, 'Feedback body for admins.')
            && str_contains($rendered, 'Feedback Sender')
            && str_contains($rendered, 'sender@sch.gr')
            && $mail->hasReplyTo('sender@sch.gr', 'Feedback Sender');
    });

    Mail::assertNotQueued(AdminFeedbackAlert::class, function (AdminFeedbackAlert $mail) use ($otherTeacher, $sender) {
        return $mail->hasTo($otherTeacher->email) || $mail->hasTo($sender->email);
    });
});

test('feedback reports a friendly error when queue dispatch fails', function () {
    $sender = User::factory()->create([
        'role' => 'teacher',
    ]);

    User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@sch.gr',
    ]);

    Mail::shouldReceive('to->queue')
        ->once()
        ->andThrow(new \RuntimeException('Queue unavailable'));

    $response = $this->actingAs($sender)->from(route('feedback.create'))->post(route('feedback.store'), [
        'title' => 'Feedback Title',
        'message' => 'Feedback body for admins.',
    ]);

    $response->assertRedirect(route('feedback.create'));
    $response->assertSessionHas('error', __('controllers.feedback_send_failed'));
});

test('feedback is rate limited after repeated submissions', function () {
    Mail::fake();

    $sender = User::factory()->create([
        'role' => 'teacher',
    ]);

    User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@sch.gr',
    ]);

    foreach (range(1, 3) as $attempt) {
        $this->actingAs($sender)->post(route('feedback.store'), [
            'title' => 'Feedback Title '.$attempt,
            'message' => 'Feedback body for admins.',
        ])->assertRedirect(route('dashboard'));
    }

    $this->actingAs($sender)->post(route('feedback.store'), [
        'title' => 'Feedback Title 4',
        'message' => 'Feedback body for admins.',
    ])->assertStatus(429);
});
