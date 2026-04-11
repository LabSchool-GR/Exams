@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5 dashboard-auth-layout justify-content-center">
        <section class="dashboard-section-card dashboard-auth-form-card w-100" style="max-width: 44rem;">
            <div class="dashboard-auth-form-card__header text-center">
                <span class="dashboard-section-card__eyebrow mx-auto">
                    <i class="fas fa-envelope-circle-check"></i>
                    {{ __('auth.verify_email_title') }}
                </span>
                <h1 class="dashboard-auth-form-card__title">{{ __('auth.verify_email_title') }}</h1>
                <p class="dashboard-auth-form-card__text">{{ __('auth.verify_email_description') }}</p>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ __('auth.verification_sent') }}</div>
                </div>
            @endif

            <div class="dashboard-form-actions justify-content-center">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-redo me-2"></i>{{ __('auth.resend_verification') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-sign-out-alt me-2"></i>{{ __('auth.logout') }}
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
