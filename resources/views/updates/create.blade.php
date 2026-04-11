@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 56rem; margin-inline: auto;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-plus-circle"></i>
                        {{ __('dashboard.updates_add') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('dashboard.updates_create') }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.updates_intro') }}</p>
                </div>

                <a href="{{ route('updates.index') }}" class="btn dashboard-btn dashboard-btn--ghost">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('dashboard.back') }}
                </a>
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

            <form method="POST" action="{{ route('updates.store') }}" class="dashboard-form-stack">
                @csrf

                <div class="dashboard-form-group">
                    <label for="description" class="dashboard-form-label">
                        <i class="fas fa-align-left text-muted"></i>{{ __('dashboard.updates_description') }}
                    </label>
                    <textarea name="description" id="description" class="form-control dashboard-form-control @error('description') is-invalid @enderror" rows="5" required>{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-group">
                    <label for="link" class="dashboard-form-label">
                        <i class="fas fa-link text-muted"></i>{{ __('dashboard.updates_link') }}
                    </label>
                    <input type="url" name="link" id="link" class="form-control dashboard-form-control @error('link') is-invalid @enderror" value="{{ old('link') }}" placeholder="https://example.com">
                    @error('link')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-save me-2"></i>{{ __('dashboard.save') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
