@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5 dashboard-auth-layout justify-content-center">
        <section class="dashboard-section-card dashboard-auth-form-card w-100" style="max-width: 34rem;">
            <div class="dashboard-auth-form-card__header">
                <span class="dashboard-section-card__eyebrow">
                    <i class="fas fa-lock"></i>
                    {{ __('auth.confirm_password_button') }}
                </span>
                <h1 class="dashboard-auth-form-card__title">{{ __('auth.confirm_password_title') }}</h1>
                <p class="dashboard-auth-form-card__text">{{ __('auth.confirm_password_description') }}</p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="dashboard-form-stack">
                @csrf

                <div class="dashboard-form-group">
                    <label for="password" class="dashboard-form-label">
                        <i class="fas fa-key text-muted"></i>{{ __('auth.confirm_password_label') }}
                    </label>
                    <input id="password" type="password" class="form-control dashboard-form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-check-circle me-2"></i>{{ __('auth.confirm_password_button') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
