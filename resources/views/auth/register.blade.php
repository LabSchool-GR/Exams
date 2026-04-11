@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5 dashboard-auth-layout">
        <div class="dashboard-auth-grid w-100">
            <section class="dashboard-hero dashboard-auth-panel">
                <div class="dashboard-hero__panel h-100">
                    <div class="dashboard-hero__surface h-100 d-flex flex-column justify-content-center">
                        <span class="dashboard-eyebrow">
                            <i class="fas fa-user-graduate"></i>
                            {{ __('auth.register_button') }}
                        </span>

                        <div class="dashboard-hero__content px-0">
                            <h1 class="dashboard-hero__title">{{ __('auth.login_panel_title') }}</h1>
                            <p class="dashboard-hero__text">{{ __('auth.login_panel_description') }}</p>
                            <p class="dashboard-hero__text">{{ __('auth.login_panel_access_note', ['domains' => config('security.registration.allowed_email_domains_display')]) }}</p>
                            <p class="dashboard-hero__text">{{ __('auth.login_panel_disclaimer') }}</p>

                            <div class="dashboard-form-panel dashboard-form-panel--compact">
                                <h2 class="dashboard-form-panel__title">{{ __('auth.register_limits_title') }}</h2>

                                <div class="dashboard-profile-summary__meta dashboard-profile-summary__meta--compact">
                                    <div class="dashboard-profile-summary__item dashboard-profile-summary__item--compact">
                                        <span>{{ __('users.max_quizzes') }}</span>
                                        <strong>1</strong>
                                    </div>
                                    <div class="dashboard-profile-summary__item dashboard-profile-summary__item--compact">
                                        <span>{{ __('users.max_questions_per_quiz') }}</span>
                                        <strong>30</strong>
                                    </div>
                                    <div class="dashboard-profile-summary__item dashboard-profile-summary__item--compact">
                                        <span>{{ __('users.max_answers_per_question') }}</span>
                                        <strong>4</strong>
                                    </div>
                                    <div class="dashboard-profile-summary__item dashboard-profile-summary__item--compact">
                                        <span>{{ __('users.max_students_per_quiz') }}</span>
                                        <strong>30</strong>
                                    </div>
                                </div>

                                <p class="dashboard-form-help mb-0">{{ __('auth.register_limits_note') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-section-card dashboard-auth-form-card">
                <div class="dashboard-auth-form-card__header">
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-address-card"></i>
                        {{ __('auth.register_button') }}
                    </span>
                    <h2 class="dashboard-auth-form-card__title">{{ __('auth.register_title') }}</h2>
                    <p class="dashboard-auth-form-card__text">{{ __('auth.register_help_text') }}</p>
                </div>

                @if(session('status'))
                    <div class="dashboard-status-card dashboard-status-card--success mb-4">
                        <i class="fas fa-check-circle"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="dashboard-form-stack">
                    @csrf

                    <div class="dashboard-form-group">
                        <label for="name" class="dashboard-form-label">
                            <i class="fas fa-user text-muted"></i>{{ __('auth.name') }}
                        </label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control dashboard-form-control @error('name') is-invalid @enderror" required autofocus>
                        @error('name')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="dashboard-form-group">
                        <label for="email" class="dashboard-form-label">
                            <i class="fas fa-envelope text-muted"></i>{{ __('auth.email_allowed_domains', ['domains' => config('security.registration.allowed_email_domains_display')]) }}
                        </label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control dashboard-form-control @error('email') is-invalid @enderror" required>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="dashboard-form-grid">
                        <div class="dashboard-form-group">
                            <label for="password" class="dashboard-form-label">
                                <i class="fas fa-key text-muted"></i>{{ __('auth.password') }}
                            </label>
                            <input type="password" id="password" name="password" class="form-control dashboard-form-control @error('password') is-invalid @enderror" required>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="dashboard-form-group">
                            <label for="password_confirmation" class="dashboard-form-label">
                                <i class="fas fa-check text-muted"></i>{{ __('auth.password_confirm') }}
                            </label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control dashboard-form-control" required>
                        </div>
                    </div>

                    <div class="form-check dashboard-form-check small">
                        <input class="form-check-input" type="checkbox" id="terms" required>
                        <label class="form-check-label" for="terms">
                            {!! __('auth.agree_terms_privacy', ['terms_url' => route('terms'), 'privacy_url' => route('privacy')]) !!}
                        </label>
                    </div>

                    @if(config('services.turnstile.enabled'))
                        <div class="dashboard-form-group">
                            <div
                                class="cf-turnstile"
                                data-turnstile-widget
                                data-sitekey="{{ config('services.turnstile.site_key') }}"
                                data-language="{{ str_replace('_', '-', app()->getLocale()) }}"
                            ></div>
                            <p class="dashboard-form-help mt-2 mb-0">{{ __('auth.turnstile_help') }}</p>
                            @error('cf-turnstile-response')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endif

                    <input type="hidden" name="role" value="teacher">

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-user-plus me-2"></i>{{ __('auth.register_button') }}
                        </button>
                    </div>
                </form>

                <div class="dashboard-auth-form-card__footer">
                    <span>{{ __('auth.already_account') }}</span>
                    <a href="{{ route('login') }}" class="dashboard-inline-link ms-1">
                        <i class="fas fa-sign-in-alt me-1"></i>{{ __('auth.login_here') }}
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
