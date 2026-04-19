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
use App\Models\User;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
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
     * Create a new teacher account restricted to configured institutional email domains.
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
                'unique:' . User::class,
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (!$this->emailBelongsToAllowedDomain((string) $value)) {
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
                    if (!$this->validateTurnstileToken((string) $value, $request->ip())) {
                        $fail(__('auth.turnstile_failed'));
                    }
                },
            ];
        }

        $request->validate($rules, [
            'cf-turnstile-response.required' => __('auth.turnstile_required'),
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'teacher',
        ]);

        $adminEmails = User::query()
            ->where('role', 'admin')
            ->whereNotNull('email')
            ->pluck('email')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (!empty($adminEmails)) {
            try {
                Mail::to($adminEmails)->queue(new AdminTeacherRegistrationAlert(
                    route('users.index'),
                    now()->toDateTimeString()
                ));
            } catch (\Throwable $e) {
                Log::error('Admin registration notification failed: ' . $e->getMessage());
            }
        }

        event(new Registered($user));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('success', __('auth.verify_email_check_inbox'));
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

        if (!$response->successful()) {
            Log::warning('Turnstile validation returned a non-success response.', [
                'status' => $response->status(),
            ]);

            return false;
        }

        $payload = $response->json();
        if (!is_array($payload)) {
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
