@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 64rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-user-plus"></i>
                        {{ __('users.create_new_user') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('users.create_user') }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('users.create_user_text') }}</p>
                </div>
                <a href="{{ route('users.index') }}" class="dashboard-secondary-button">
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

            <form method="POST" action="{{ route('users.store') }}" class="dashboard-form-grid">
                @csrf

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="name">{{ __('users.name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required class="dashboard-input">
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="email">{{ __('users.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required class="dashboard-input">
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="role">{{ __('users.role') }}</label>
                    <select id="role" name="role" class="dashboard-select" required>
                        <option value="teacher" @selected(old('role') === 'teacher')>{{ __('users.teacher') }}</option>
                        <option value="admin" @selected(old('role') === 'admin')>{{ __('users.admin') }}</option>
                    </select>
                </div>

                <div class="dashboard-form-panel">
                    <h2 class="dashboard-form-panel__title">{{ __('users.limits') }}</h2>
                    <p class="dashboard-page-header__text">{{ __('users.limits_text') }}</p>
                    <div class="dashboard-form-grid dashboard-form-grid--compact">
                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_quizzes">{{ __('users.max_quizzes') }}</label>
                            <input id="max_quizzes" type="number" min="0" name="max_quizzes" value="{{ old('max_quizzes', 0) }}" class="dashboard-input">
                        </div>

                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_questions_per_quiz">{{ __('users.max_questions_per_quiz') }}</label>
                            <input id="max_questions_per_quiz" type="number" min="0" name="max_questions_per_quiz" value="{{ old('max_questions_per_quiz', 0) }}" class="dashboard-input">
                        </div>

                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_answers_per_question">{{ __('users.max_answers_per_question') }}</label>
                            <input id="max_answers_per_question" type="number" min="0" name="max_answers_per_question" value="{{ old('max_answers_per_question', 0) }}" class="dashboard-input">
                        </div>

                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_students_per_quiz">{{ __('users.max_students_per_quiz') }}</label>
                            <input id="max_students_per_quiz" type="number" min="0" name="max_students_per_quiz" value="{{ old('max_students_per_quiz', 0) }}" class="dashboard-input">
                        </div>
                    </div>
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="password">{{ __('users.password') }}</label>
                    <input id="password" type="password" name="password" required class="dashboard-input">
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="password_confirmation">{{ __('users.confirm_password') }}</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required class="dashboard-input">
                </div>

                <div class="dashboard-form-actions">
                    <button type="submit" class="dashboard-primary-button">
                        <i class="fas fa-save me-2"></i>{{ __('users.create_user') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
