<?php

/**
 * RegisteredUserController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminTeacherRegistrationAlert;
use App\Mail\PendingRegistrationVerification;
use App\Models\PendingRegistration;
use App\Models\User;
use Closure;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Registers new teacher accounts and starts the email verification flow.
 */
class RegisteredUserController extends Controller
{
    /**
     * Display the teacher registration form.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Start a teacher registration after validating the institutional email domain.
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:'.User::class,
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (! $this->emailBelongsToAllowedDomain((string) $value)) {
                        $fail(__('auth.allowed_email_domain', [
                            'domains' => $this->allowedRegistrationDomainsDisplay(),
                        ]));
                    }
                },
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($this->turnstileEnabled()) {
            $rules['cf-turnstile-response'] = [
                'bail',
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                    if (! $this->validateTurnstileToken((string) $value, $request->ip())) {
                        $fail(__('auth.turnstile_failed'));
                    }
                },
            ];
        }

        $request->validate($rules, [
            'cf-turnstile-response.required' => __('auth.turnstile_required'),
        ]);

        $email = strtolower((string) $request->email);
        $this->ensureRegistrationEmailRateLimitAvailable($email);

        RateLimiter::hit(
            $this->registrationEmailRateLimitKey($email),
            $this->registrationEmailRateLimitDecaySeconds()
        );

        $token = Str::random(64);
        $expiresAt = $this->pendingRegistrationExpiresAt();

        $pendingRegistration = PendingRegistration::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) $request->name,
                'password' => Hash::make((string) $request->password),
                'token_hash' => hash('sha256', $token),
                'expires_at' => $expiresAt,
            ]
        );

        try {
            Mail::to($pendingRegistration->email)->send(new PendingRegistrationVerification(
                $pendingRegistration->name,
                route('register.verify', ['token' => $token]),
                $expiresAt->toDateTimeString()
            ));
        } catch (\Throwable $e) {
            $pendingRegistration->delete();

            Log::error('Pending registration verification email failed.', [
                'message' => $e->getMessage(),
                'email_hash' => sha1($email),
            ]);

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->with('error', __('auth.pending_registration_send_failed'));
        }

        return back()->with('status', __('auth.pending_registration_check_inbox'));
    }

    /**
     * Create the teacher account only after the email-owned registration token is confirmed.
     */
    public function verify(Request $request, string $token): RedirectResponse
    {
        $pendingRegistration = PendingRegistration::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $pendingRegistration) {
            return redirect()->route('register')
                ->with('error', __('auth.pending_registration_invalid'));
        }

        if ($pendingRegistration->isExpired()) {
            $pendingRegistration->delete();

            return redirect()->route('register')
                ->with('error', __('auth.pending_registration_expired'));
        }

        if (User::query()->where('email', $pendingRegistration->email)->exists()) {
            $pendingRegistration->delete();

            return redirect()->route('login')
                ->with('error', __('auth.pending_registration_already_registered'));
        }

        $user = DB::transaction(function () use ($pendingRegistration): User {
            $user = User::create([
                'name' => $pendingRegistration->name,
                'email' => $pendingRegistration->email,
                'password' => $pendingRegistration->password,
                'role' => 'teacher',
            ]);

            $user->markEmailAsVerified();

            $pendingRegistration->delete();

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        event(new Verified($user));

        $this->notifyAdminsOfTeacherRegistration();

        return redirect()->route('dashboard')
            ->with('success', __('auth.pending_registration_confirmed'));
    }

    /**
     * Prevent repeated email delivery attempts against the same recipient address.
     */
    private function ensureRegistrationEmailRateLimitAvailable(string $email): void
    {
        if (! RateLimiter::tooManyAttempts(
            $this->registrationEmailRateLimitKey($email),
            $this->registrationEmailRateLimitMaxAttempts()
        )) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => __('auth.registration_email_rate_limited'),
        ]);
    }

    private function registrationEmailRateLimitKey(string $email): string
    {
        return 'registration-email:'.sha1($email);
    }

    private function registrationEmailRateLimitMaxAttempts(): int
    {
        [$maxAttempts] = $this->registrationEmailThrottleParts();

        return $maxAttempts;
    }

    private function registrationEmailRateLimitDecaySeconds(): int
    {
        [, $decayMinutes] = $this->registrationEmailThrottleParts();

        return $decayMinutes * 60;
    }

    /**
     * Parse the same "max,minutes" format used by Laravel's throttle middleware.
     *
     * @return array{0: int, 1: int}
     */
    private function registrationEmailThrottleParts(): array
    {
        $rawThrottle = (string) config('security.throttle.registration_email_attempts', '3,60');
        $parts = array_map('trim', explode(',', $rawThrottle, 2));

        return [
            max(1, (int) ($parts[0] ?? 3)),
            max(1, (int) ($parts[1] ?? 60)),
        ];
    }

    private function pendingRegistrationExpiresAt(): Carbon
    {
        $hours = max(1, (int) config('security.registration.pending_expiration_hours', 24));

        return now()->addHours($hours);
    }

    /**
     * Notify administrators after a verified teacher account exists.
     */
    private function notifyAdminsOfTeacherRegistration(): void
    {
        $adminEmails = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($adminEmails)) {
            return;
        }

        try {
            Mail::to($adminEmails)->queue(new AdminTeacherRegistrationAlert(
                route('users.index'),
                now()->toDateTimeString()
            ));
        } catch (\Throwable $e) {
            Log::error('Admin registration notification failed: '.$e->getMessage());
        }
    }

    /**
     * Determine whether the email uses one of the configured registration domains.
     */
    private function emailBelongsToAllowedDomain(string $email): bool
    {
        $parts = explode('@', strtolower($email));

        if (count($parts) !== 2 || $parts[1] === '') {
            return false;
        }

        return in_array($parts[1], $this->allowedRegistrationDomains(), true);
    }

    /**
     * Get the configured list of registration email domains.
     *
     * @return array<int, string>
     */
    private function allowedRegistrationDomains(): array
    {
        return config('security.registration.allowed_email_domains', ['sch.gr']);
    }

    /**
     * Get the configured registration email domains formatted for display.
     */
    private function allowedRegistrationDomainsDisplay(): string
    {
        return config('security.registration.allowed_email_domains_display', '@sch.gr');
    }

    /**
     * Determine whether registration should enforce Turnstile validation.
     */
    private function turnstileEnabled(): bool
    {
        return (bool) config('services.turnstile.enabled', false)
            && filled((string) config('services.turnstile.site_key'))
            && filled((string) config('services.turnstile.secret_key'));
    }

    /**
     * Verify the submitted Turnstile token with Cloudflare.
     */
    private function validateTurnstileToken(string $token, ?string $remoteIp): bool
    {
        if ($token === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(10)
                ->post((string) config('services.turnstile.verify_url'), array_filter([
                    'secret' => (string) config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $remoteIp,
                ], static fn (mixed $value): bool => filled($value)));
        } catch (\Throwable $exception) {
            Log::warning('Turnstile validation request failed.', [
                'message' => $exception->getMessage(),
            ]);

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Turnstile validation returned a non-success response.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            Log::warning('Turnstile validation returned an invalid payload.');

            return false;
        }

        if (($payload['success'] ?? false) === true) {
            return true;
        }

        Log::notice('Turnstile validation rejected a registration attempt.', [
            'error_codes' => $payload['error-codes'] ?? [],
        ]);

        return false;
    }
}
