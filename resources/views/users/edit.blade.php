@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 64rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-user-gear"></i>
                        {{ __('quizzes_cards.edit_title') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $user->name }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('users.edit_user_text') }}</p>
                </div>
                <a href="{{ route('users.index') }}" class="dashboard-secondary-button">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('common.back') }}
                </a>
            </div>

            @if(session('success'))
                <div class="dashboard-status-card dashboard-status-card--success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="dashboard-status-card dashboard-status-card--danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="dashboard-status-card dashboard-status-card--danger">
                    <ul class="dashboard-status-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('users.update', $user) }}" class="dashboard-form-grid">
                @csrf
                @method('PUT')

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="name">{{ __('users.name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required class="dashboard-input">
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="email">{{ __('users.email') }}</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required class="dashboard-input">
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="role">{{ __('users.role') }}</label>
                    <select id="role" name="role" class="dashboard-select" required>
                        <option value="teacher" @selected(old('role', $user->role) === 'teacher')>{{ __('users.teacher') }}</option>
                        <option value="admin" @selected(old('role', $user->role) === 'admin')>{{ __('users.admin') }}</option>
                    </select>
                </div>

                <div class="dashboard-form-panel">
                    <h2 class="dashboard-form-panel__title">{{ __('users.limits') }}</h2>
                    <p class="dashboard-page-header__text">{{ __('users.limits_text') }}</p>
                    <div class="dashboard-form-grid dashboard-form-grid--compact">
                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_quizzes">{{ __('users.max_quizzes') }}</label>
                            <input id="max_quizzes" type="number" min="0" name="max_quizzes" value="{{ old('max_quizzes', $user->max_quizzes) }}" class="dashboard-input">
                        </div>

                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_questions_per_quiz">{{ __('users.max_questions_per_quiz') }}</label>
                            <input id="max_questions_per_quiz" type="number" min="0" name="max_questions_per_quiz" value="{{ old('max_questions_per_quiz', $user->max_questions_per_quiz) }}" class="dashboard-input">
                        </div>

                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_answers_per_question">{{ __('users.max_answers_per_question') }}</label>
                            <input id="max_answers_per_question" type="number" min="0" name="max_answers_per_question" value="{{ old('max_answers_per_question', $user->max_answers_per_question) }}" class="dashboard-input">
                        </div>

                        <div class="dashboard-form-field">
                            <label class="dashboard-form-label" for="max_students_per_quiz">{{ __('users.max_students_per_quiz') }}</label>
                            <input id="max_students_per_quiz" type="number" min="0" name="max_students_per_quiz" value="{{ old('max_students_per_quiz', $user->max_students_per_quiz) }}" class="dashboard-input">
                        </div>
                    </div>
                </div>

                <div class="dashboard-form-actions">
                    <button type="submit" class="dashboard-primary-button">
                        <i class="fas fa-save me-2"></i>{{ __('users.update_user') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
