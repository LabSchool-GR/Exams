<?php

/**
 * RegistrationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Mail\AdminTeacherRegistrationAlert;
use App\Mail\PendingRegistrationVerification;
use App\Models\PendingRegistration;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

test('registration screen can be rendered', function () {
    /** @var TestCase $this */
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registration screen renders a turnstile widget when enabled', function () {
    /** @var TestCase $this */
    config()->set('services.turnstile.enabled', true);
    config()->set('services.turnstile.site_key', 'site-key');
    config()->set('services.turnstile.secret_key', 'secret-key');

    $response = $this->get('/register');

    $response->assertStatus(200);
    $response->assertSee('data-turnstile-widget', false);
    $response->assertSee('data-sitekey="site-key"', false);
});

test('new users can register with the default allowed domain', function () {
    /** @var TestCase $this */
    Mail::fake();

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', [
        'email' => 'test@sch.gr',
    ]);
    $this->assertDatabaseHas('pending_registrations', [
        'email' => 'test@sch.gr',
    ]);
    $response->assertRedirect('/register');
    $response->assertSessionHas('status', __('auth.pending_registration_check_inbox'));

    Mail::assertSent(PendingRegistrationVerification::class, function (PendingRegistrationVerification $mail) {
        return $mail->hasTo('test@sch.gr')
            && str_contains($mail->verificationUrl, '/register/verify/');
    });
});

test('registration removes the pending record when confirmation email dispatch fails', function () {
    /** @var TestCase $this */
    Mail::shouldReceive('to')
        ->once()
        ->with('test@sch.gr')
        ->andThrow(new RuntimeException('SMTP unavailable'));

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $this->assertDatabaseMissing('users', [
        'email' => 'test@sch.gr',
    ]);
    $this->assertDatabaseMissing('pending_registrations', [
        'email' => 'test@sch.gr',
    ]);
    $response->assertRedirect('/register');
    $response->assertSessionHas('error', __('auth.pending_registration_send_failed'));
});

test('pending registration verification creates a verified user and signs them in', function () {
    /** @var TestCase $this */
    Mail::fake();

    $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $verificationUrl = '';
    Mail::assertSent(PendingRegistrationVerification::class, function (PendingRegistrationVerification $mail) use (&$verificationUrl) {
        $verificationUrl = $mail->verificationUrl;

        return $mail->hasTo('test@sch.gr');
    });

    $response = $this->get((string) parse_url($verificationUrl, PHP_URL_PATH));

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
    $response->assertSessionHas('success', __('auth.pending_registration_confirmed'));
    $this->assertDatabaseHas('users', [
        'email' => 'test@sch.gr',
        'role' => 'teacher',
    ]);
    $this->assertNotNull(User::where('email', 'test@sch.gr')->firstOrFail()->email_verified_at);
    $this->assertDatabaseMissing('pending_registrations', [
        'email' => 'test@sch.gr',
    ]);
});

test('new users can register with any configured allowed email domain', function () {
    /** @var TestCase $this */
    Mail::fake();

    config()->set('security.registration.allowed_email_domains', ['sch.gr', 'edu.gr']);
    config()->set('security.registration.allowed_email_domains_display', '@sch.gr, @edu.gr');

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@edu.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertGuest();
    $this->assertDatabaseHas('pending_registrations', [
        'email' => 'test@edu.gr',
    ]);
    $response->assertRedirect('/register');
    $response->assertSessionHas('status', __('auth.pending_registration_check_inbox'));
});

test('registration rejects email addresses outside the configured allowed domains', function () {
    /** @var TestCase $this */
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
    /** @var TestCase $this */
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
    /** @var TestCase $this */
    Mail::fake();

    config()->set('services.turnstile.enabled', true);
    config()->set('services.turnstile.site_key', 'site-key');
    config()->set('services.turnstile.secret_key', 'secret-key');
    config()->set('services.turnstile.verify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');

    Http::fake([
        'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
            'success' => true,
        ], 200),
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
        'cf-turnstile-response' => 'test-token',
    ]);

    $this->assertGuest();
    $this->assertDatabaseHas('pending_registrations', [
        'email' => 'test@sch.gr',
    ]);
    $response->assertRedirect('/register');
    $response->assertSessionHas('status', __('auth.pending_registration_check_inbox'));
});

test('registration rejects invalid turnstile responses', function () {
    /** @var TestCase $this */
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
    /** @var TestCase $this */
    Mail::fake();

    $admin = User::factory()->create([
        'role' => 'admin',
        'email' => 'admin@sch.gr',
    ]);

    $teacher = User::factory()->create([
        'role' => 'teacher',
        'email' => 'teacher@sch.gr',
    ]);

    $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'test@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    Mail::assertNotQueued(AdminTeacherRegistrationAlert::class);

    $verificationUrl = '';
    Mail::assertSent(PendingRegistrationVerification::class, function (PendingRegistrationVerification $mail) use (&$verificationUrl) {
        $verificationUrl = $mail->verificationUrl;

        return $mail->hasTo('test@sch.gr');
    });

    $this->get((string) parse_url($verificationUrl, PHP_URL_PATH))
        ->assertRedirect(route('dashboard', absolute: false));

    Mail::assertQueued(AdminTeacherRegistrationAlert::class, function (AdminTeacherRegistrationAlert $mail) use ($admin) {
        $rendered = $mail->render();

        return $mail->hasTo($admin->email)
            && ! str_contains($rendered, 'Test User')
            && ! str_contains($rendered, 'test@sch.gr')
            && str_contains($rendered, route('users.index'));
    });

    Mail::assertNotQueued(AdminTeacherRegistrationAlert::class, function (AdminTeacherRegistrationAlert $mail) use ($teacher) {
        return $mail->hasTo($teacher->email) || $mail->hasTo('test@sch.gr');
    });
});

test('registration is rate limited by recipient email address', function () {
    /** @var TestCase $this */
    Mail::fake();
    config()->set('security.throttle.registration_email_attempts', '2,60');

    for ($i = 0; $i < 2; $i++) {
        $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'repeat@sch.gr',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/register');
    }

    $response = $this->from('/register')->post('/register', [
        'name' => 'Test User',
        'email' => 'repeat@sch.gr',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/register');
    $response->assertSessionHasErrors([
        'email' => __('auth.registration_email_rate_limited'),
    ]);
    $this->assertDatabaseCount('pending_registrations', 1);
});

test('expired pending registration links are rejected and removed', function () {
    /** @var TestCase $this */
    $token = 'expired-token';

    PendingRegistration::create([
        'name' => 'Expired User',
        'email' => 'expired@sch.gr',
        'password' => Hash::make('password'),
        'token_hash' => hash('sha256', $token),
        'expires_at' => now()->subMinute(),
    ]);

    $response = $this->get(route('register.verify', ['token' => $token]));

    $this->assertGuest();
    $response->assertRedirect(route('register', absolute: false));
    $response->assertSessionHas('error', __('auth.pending_registration_expired'));
    $this->assertDatabaseMissing('pending_registrations', [
        'email' => 'expired@sch.gr',
    ]);
});

test('pending registration pruning deletes expired requests only', function () {
    /** @var TestCase $this */
    PendingRegistration::create([
        'name' => 'Expired User',
        'email' => 'expired@sch.gr',
        'password' => Hash::make('password'),
        'token_hash' => hash('sha256', 'expired-token'),
        'expires_at' => now()->subMinute(),
    ]);

    PendingRegistration::create([
        'name' => 'Fresh User',
        'email' => 'fresh@sch.gr',
        'password' => Hash::make('password'),
        'token_hash' => hash('sha256', 'fresh-token'),
        'expires_at' => now()->addDay(),
    ]);

    $this->artisan('registrations:prune-pending')
        ->expectsOutputToContain('Deleted 1 expired pending registration')
        ->assertSuccessful();

    $this->assertDatabaseMissing('pending_registrations', [
        'email' => 'expired@sch.gr',
    ]);
    $this->assertDatabaseHas('pending_registrations', [
        'email' => 'fresh@sch.gr',
    ]);
});

test('registration is rate limited after repeated attempts', function () {
    /** @var TestCase $this */
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
