@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5 dashboard-auth-layout justify-content-center">
        <section class="dashboard-section-card dashboard-auth-form-card w-100" style="max-width: 34rem;">
            <div class="dashboard-auth-form-card__header">
                <span class="dashboard-section-card__eyebrow">
                    <i class="fas fa-unlock-alt"></i>
                    {{ __('auth.send_reset_link') }}
                </span>
                <h1 class="dashboard-auth-form-card__title">{{ __('auth.forgot_password_title') }}</h1>
                <p class="dashboard-auth-form-card__text">{{ __('auth.forgot_password_description') }}</p>
            </div>

            @if (session('status'))
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="dashboard-form-stack">
                @csrf

                <div class="dashboard-form-group">
                    <label for="email" class="dashboard-form-label">
                        <i class="fas fa-envelope text-muted"></i>{{ __('auth.email_label') }}
                    </label>
                    <input id="email" type="email" class="form-control dashboard-form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="example@domain.com">
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('auth.send_reset_link') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
