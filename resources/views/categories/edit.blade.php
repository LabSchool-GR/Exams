@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 42rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-pen-to-square"></i>
                        {{ __('quizzes.category_description') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $category->name }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('quizzes.category_edit_text') }}</p>
                </div>
                <a href="{{ route('categories.index') }}" class="dashboard-secondary-button">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('common.back') }}
                </a>
            </div>

            @if($errors->any())
                <div class="dashboard-status-card dashboard-status-card--danger">
                    <ul class="dashboard-status-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('categories.update', $category) }}" class="dashboard-form-grid">
                @csrf
                @method('PUT')

                <div class="dashboard-form-field dashboard-form-field--full">
                    <label class="dashboard-form-label" for="name">{{ __('quizzes.category_name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" required class="dashboard-input" placeholder="{{ __('quizzes_cards.example_math') }}">
                </div>

                <div class="dashboard-form-actions">
                    <button type="submit" class="dashboard-primary-button">
                        <i class="fas fa-save me-2"></i>{{ __('common.save') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
