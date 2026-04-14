@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-user-circle"></i>
                        {{ __('dashboard.profile_eyebrow') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('quizzes_cards.title_profile') }}</h1>
                    <p class="dashboard-page-header__text mw-100">{{ __('dashboard.limits_intro') }}</p>
                </div>

            </div>

            <div class="dashboard-profile-grid">
                <aside class="dashboard-profile-sidebar">
                    <div class="dashboard-profile-summary">
                        <span class="dashboard-eyebrow">
                            <i class="fas fa-id-badge"></i>
                            {{ __('dashboard.profile_title') }}
                        </span>
                        <h2 class="dashboard-profile-summary__title">{{ $user->name }}</h2>
                        <p class="dashboard-profile-summary__text">{{ $user->email }}</p>

                        <div class="dashboard-profile-summary__meta">
                            <div class="dashboard-profile-summary__item">
                                <span>{{ __('dashboard.user_role') }}</span>
                                <strong>{{ $user->role === 'admin' ? __('quizzes.admin') : __('quizzes.teacher') }}</strong>
                            </div>
                            <div class="dashboard-profile-summary__item">
                                <span>{{ __('dashboard.limit_quizzes') }}</span>
                                <strong>{{ $user->max_quizzes }}</strong>
                            </div>
                            <div class="dashboard-profile-summary__item">
                                <span>{{ __('dashboard.limit_questions_per_quiz') }}</span>
                                <strong>{{ $user->max_questions_per_quiz }}</strong>
                            </div>
                            <div class="dashboard-profile-summary__item">
                                <span>{{ __('dashboard.limit_students_per_quiz') }}</span>
                                <strong>{{ $user->max_students_per_quiz }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="dashboard-content-stack">
                    @include('profile.partials.update-profile-information-form')
                    @include('profile.partials.update-password-form')
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
