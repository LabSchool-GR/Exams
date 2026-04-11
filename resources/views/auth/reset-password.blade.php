@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5 dashboard-auth-layout justify-content-center">
        <section class="dashboard-section-card dashboard-auth-form-card w-100" style="max-width: 36rem;">
            <div class="dashboard-auth-form-card__header">
                <span class="dashboard-section-card__eyebrow">
                    <i class="fas fa-sync-alt"></i>
                    {{ __('auth.reset_button') }}
                </span>
                <h1 class="dashboard-auth-form-card__title">{{ __('auth.reset_password_title') }}</h1>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="dashboard-form-stack">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="dashboard-form-group">
                    <label for="email" class="dashboard-form-label">
                        <i class="fas fa-envelope text-muted"></i>{{ __('auth.email') }}
                    </label>
                    <input id="email" type="email" class="form-control dashboard-form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $request->email) }}" required autocomplete="username" autofocus>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-grid">
                    <div class="dashboard-form-group">
                        <label for="password" class="dashboard-form-label">
                            <i class="fas fa-lock text-muted"></i>{{ __('auth.new_password') }}
                        </label>
                        <input id="password" type="password" class="form-control dashboard-form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="dashboard-form-group">
                        <label for="password_confirmation" class="dashboard-form-label">
                            <i class="fas fa-shield text-muted"></i>{{ __('auth.confirm_password') }}
                        </label>
                        <input id="password_confirmation" type="password" class="form-control dashboard-form-control @error('password_confirmation') is-invalid @enderror" name="password_confirmation" required autocomplete="new-password">
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-check-circle me-2"></i>{{ __('auth.reset_button') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
