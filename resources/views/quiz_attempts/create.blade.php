@extends('layouts.app')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="dashboard-section-card dashboard-auth-form-card">
        <div class="dashboard-page-header dashboard-page-header--stack dashboard-auth-form-card__header">
            <div>
                <span class="dashboard-page-header__eyebrow">{{ __('quiz_attempts.start_attempt') }}</span>
                <h1 class="dashboard-page-header__title">{{ $quiz->title }}</h1>
                <p class="dashboard-page-header__subtitle">{{ __('quiz_attempts.enter_student_info') }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('quiz_attempts.store', $quiz) }}" class="dashboard-auth-form-card__form">
            @csrf

            <div class="dashboard-form-grid">
                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="student_name">{{ __('quiz_attempts.student_name') }}</label>
                    <input
                        id="student_name"
                        type="text"
                        name="student_name"
                        value="{{ old('student_name') }}"
                        required
                        autofocus
                        class="dashboard-input @error('student_name') is-invalid @enderror"
                    >
                    @error('student_name')
                        <p class="dashboard-form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="student_code">{{ __('quiz_attempts.student_code') }}</label>
                    <input
                        id="student_code"
                        type="password"
                        name="student_code"
                        maxlength="4"
                        inputmode="numeric"
                        pattern="[0-9]{4}"
                        required
                        class="dashboard-input @error('student_code') is-invalid @enderror"
                    >
                    @error('student_code')
                        <p class="dashboard-form-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="dashboard-auth-form-card__actions">
                <button type="submit" class="dashboard-primary-button">
                    {{ __('quiz_attempts.start_quiz') }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
