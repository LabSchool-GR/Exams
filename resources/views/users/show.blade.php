@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 56rem; margin-inline: auto;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-id-card"></i>
                        {{ __('quizzes_cards.user_details') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $user->name }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('users.user_details_text') }}</p>
                </div>
                <a href="{{ route('users.index') }}" class="dashboard-secondary-button">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('common.back') }}
                </a>
            </div>

            <div class="dashboard-profile-summary__meta">
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.name') }}</span>
                    <strong>{{ $user->name }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.email') }}</span>
                    <strong>{{ $user->email }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.role') }}</span>
                    <strong>{{ __('users.' . $user->role) }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.registered_at') }}</span>
                    <strong>{{ optional($user->created_at)->format('d/m/Y H:i') }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.max_quizzes') }}</span>
                    <strong>{{ $user->max_quizzes }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.max_questions_per_quiz') }}</span>
                    <strong>{{ $user->max_questions_per_quiz }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.max_answers_per_question') }}</span>
                    <strong>{{ $user->max_answers_per_question }}</strong>
                </div>
                <div class="dashboard-profile-summary__item">
                    <span>{{ __('users.max_students_per_quiz') }}</span>
                    <strong>{{ $user->max_students_per_quiz }}</strong>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
