<?php

/**
 * RegistrationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Mail\AdminTeacherRegistrationAlert;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registration screen renders a turnstile widget when enabled', function () {
    config()->set('services.turnstile.enabled', true);
    config()->set('services.turnstile.site_key', 'site-key');
    config()->set('services.turnstile.secret_key', 'secret-key');

    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertSee('data-turnstile-widget', false);
    $response->assertSee('data-sitekey="site-key"', false);
});

test('new users can register with the default allowed domain', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('new users can register with any configured allowed email domain', function () {
    config()->set('security.registration.allowed_email_domains', ['sch.gr', 'edu.gr']);
    config()->set('security.registration.allowed_email_domains_display', '@sch.gr, @edu.gr');

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@edu.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('registration rejects email addresses outside the configured allowed domains', function () {
    config()->set('security.registration.allowed_email_domains', ['sch.gr', 'edu.gr']);
    config()->set('security.registration.allowed_email_domains_display', '@sch.gr, @edu.gr');

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors([
        'email' => __('auth.allowed_email_domain', ['domains' => '@sch.gr, @edu.gr']),
    ]);
    $this->assertGuest();
});

test('registration requires a turnstile response when turnstile is enabled', function () {
    config()->set('services.turnstile.enabled', true);
    config()->set('services.turnstile.site_key', 'site-key');
    config()->set('services.turnstile.secret_key', 'secret-key');

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors([
        'cf-turnstile-response' => __('auth.turnstile_required'),
    ]);
    $this->assertGuest();
});

test('registration succeeds when turnstile verification passes', function () {
    config()->set('services.turnstile.enabled', true);
    config()->set('services.turnstile.site_key', 'site-key');
    config()->set('services.turnstile.secret_key', 'secret-key');
    config()->set('services.turnstile.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
        ], 200),
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'test-token',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));
});

test('registration rejects invalid turnstile responses', function () {
    config()->set('services.turnstile.enabled', true);
    config()->set('services.turnstile.site_key', 'site-key');
    config()->set('services.turnstile.secret_key', 'secret-key');
    config()->set('services.turnstile.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => false,
            'error-codes' => ['timeout-or-duplicate'],
        ], 200),
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'expired-token',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors([
        'cf-turnstile-response' => __('auth.turnstile_failed'),
    ]);
    $this->assertGuest();
});

test('registration notifies only admins without personal data in the email body', function () {
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@sch.gr',
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'email' => 'teacher@sch.gr',
    ]);

    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('verification.notice', absolute: false));

    Mail::assertQueued(AdminTeacherRegistrationAlert::class, function (AdminTeacherRegistrationAlert $mail) use ($admin) {
        $rendered = $mail->render();

        return $mail->hasTo($admin->email)
            && !str_contains($rendered, 'Test User')
            && !str_contains($rendered, 'test@sch.gr')
            && str_contains($rendered, route('users.index'));
    });

    Mail::assertNotQueued(AdminTeacherRegistrationAlert::class, function (AdminTeacherRegistrationAlert $mail) use ($teacher) {
        return $mail->hasTo($teacher->email) || $mail->hasTo('test@sch.gr');
    });
});

test('registration is rate limited after repeated attempts', function () {
    for ($i = 0; $i < 5; $i++) {
        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => "blocked{$i}@example.com",
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/register');
    }

    $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'blocked-final@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertStatus(429);
});

