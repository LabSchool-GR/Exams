@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 56rem; margin-inline: auto;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-comment-dots"></i>
                        {{ __('dashboard.submit_feedback') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('dashboard.submit_feedback') }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.resource_feedback_text') }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="dashboard-status-card dashboard-status-card--danger mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('feedback.store') }}" class="dashboard-form-stack">
                @csrf

                <div class="dashboard-form-group">
                    <label for="title" class="dashboard-form-label">
                        {{ __('dashboard.feedback_title') }}
                    </label>
                    <input type="text" name="title" id="title" class="form-control dashboard-form-control @error('title') is-invalid @enderror" required value="{{ old('title') }}">
                    @error('title')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-group">
                    <label for="message" class="dashboard-form-label">
                        {{ __('dashboard.feedback_message') }}
                    </label>
                    <textarea name="message" id="message" class="form-control dashboard-form-control @error('message') is-invalid @enderror" rows="6" required>{{ old('message') }}</textarea>
                    @error('message')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-actions">
                    <a href="{{ route('dashboard') }}" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('dashboard.back') }}
                    </a>

                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('dashboard.feedback_submit') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
