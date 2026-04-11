@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5 dashboard-auth-layout">
        <div class="dashboard-auth-grid w-100">
            <section class="dashboard-hero dashboard-auth-panel">
                <div class="dashboard-hero__panel h-100">
                    <div class="dashboard-hero__surface h-100 d-flex flex-column justify-content-center">
                        <span class="dashboard-eyebrow">
                            <i class="fas fa-shield-halved"></i>
                            {{ __('dashboard.subtitle') }}
                        </span>

                        <div class="dashboard-hero__content px-0">
                            <h1 class="dashboard-hero__title">{{ __('auth.login_panel_title') }}</h1>
                            <p class="dashboard-hero__text">{{ __('auth.login_panel_description') }}</p>
                            <p class="dashboard-hero__text">{{ __('auth.login_panel_access_note', ['domains' => config('security.registration.allowed_email_domains_display')]) }}</p>
                            <p class="dashboard-hero__text">{{ __('auth.login_panel_disclaimer') }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="dashboard-section-card dashboard-auth-form-card">
                <div class="dashboard-auth-form-card__header">
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-right-to-bracket"></i>
                        {{ __('auth.login_button') }}
                    </span>
                    <h2 class="dashboard-auth-form-card__title">{{ __('auth.login_title') }}</h2>
                    <p class="dashboard-auth-form-card__text">{{ __('auth.login_help_text') }}</p>
                </div>

                @if(session('status'))
                    <div class="dashboard-status-card dashboard-status-card--success mb-4">
                        <i class="fas fa-check-circle"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="dashboard-form-stack">
                    @csrf

                    <div class="dashboard-form-group">
                        <label for="email" class="dashboard-form-label">
                            <i class="fas fa-envelope text-muted"></i>{{ __('auth.email') }}
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control dashboard-form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            required
                            autofocus
                        >
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="dashboard-form-group">
                        <label for="password" class="dashboard-form-label">
                            <i class="fas fa-key text-muted"></i>{{ __('auth.password') }}
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control dashboard-form-control @error('password') is-invalid @enderror"
                            required
                        >
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check dashboard-form-check">
                        <input class="form-check-input" type="checkbox" id="remember_me" name="remember">
                        <label class="form-check-label" for="remember_me">{{ __('auth.remember') }}</label>
                    </div>

                    <div class="dashboard-form-actions">
                        @if (Route::has('password.request'))
                            <a class="dashboard-inline-link" href="{{ route('password.request') }}">
                                <i class="fas fa-question-circle me-1"></i>{{ __('auth.forgot_password') }}
                            </a>
                        @endif

                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-sign-in-alt me-2"></i>{{ __('auth.login_button') }}
                        </button>
                    </div>
                </form>

                <div class="dashboard-auth-form-card__footer">
                    <span>{{ __('auth.no_account') }}</span>
                    <a href="{{ route('register') }}" class="dashboard-inline-link ms-1">
                        <i class="fas fa-user-plus me-1"></i>{{ __('auth.create_account') }}
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
