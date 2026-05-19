@extends('layouts.navigation')

@section('content')
@php
    $user = Auth::user();
    $roleLabel = $user->role === 'admin' ? __('quizzes.admin') : __('quizzes.teacher');
    $totalParticipants = (int) $quizzes->sum('student_count');
    $totalQuestions = (int) $quizzes->sum('question_count');
    $totalQuizCount = $quizzes->count();
@endphp

<div class="dashboard-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-hero mb-4 mb-lg-5">
            <div class="dashboard-hero__panel">
                <div class="dashboard-hero__surface">
                    <div class="dashboard-hero__grid">
                        <div class="dashboard-hero__content">
                            <span class="dashboard-eyebrow">
                                <i class="fas fa-compass"></i>
                                {{ __('dashboard.subtitle') }}
                            </span>

                            <h1 class="dashboard-hero__title">
                                {{ __('dashboard.welcome', ['name' => $user->name]) }}
                            </h1>

                            <p class="dashboard-hero__text">
                                {{ __('dashboard.overview_text') }}
                            </p>

                            <div class="dashboard-hero__actions">
                                <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--primary">
                                    <i class="fas fa-layer-group me-2"></i>{{ __('navigation.my_quizzes') }}
                                </a>
                                <a href="{{ route('feedback.create') }}" class="btn dashboard-btn dashboard-btn--ghost">
                                    <i class="fas fa-comment-dots me-2"></i>{{ __('dashboard.submit_feedback') }}
                                </a>
                            </div>
                        </div>

                        <aside class="dashboard-identity-card">
                            <div class="dashboard-identity-card__header">
                                <h2 class="dashboard-identity-card__title">{{ __('dashboard.limits_eyebrow') }}</h2>
                                <span class="dashboard-role-pill">
                                    <i class="fas fa-shield-halved me-2"></i>{{ $roleLabel }}
                                </span>
                            </div>

                            <div class="dashboard-identity-list">
                                <div class="dashboard-identity-item">
                                    <span class="dashboard-identity-item__label">{{ __('dashboard.limit_quizzes') }}</span>
                                    <strong class="dashboard-identity-item__value">{{ $user->max_quizzes }}</strong>
                                </div>
                                <div class="dashboard-identity-item">
                                    <span class="dashboard-identity-item__label">{{ __('dashboard.limit_questions_per_quiz') }}</span>
                                    <strong class="dashboard-identity-item__value">{{ $user->max_questions_per_quiz }}</strong>
                                </div>
                                <div class="dashboard-identity-item">
                                    <span class="dashboard-identity-item__label">{{ __('dashboard.limit_answers_per_question') }}</span>
                                    <strong class="dashboard-identity-item__value">{{ $user->max_answers_per_question }}</strong>
                                </div>
                                <div class="dashboard-identity-item">
                                    <span class="dashboard-identity-item__label">{{ __('dashboard.limit_students_per_quiz') }}</span>
                                    <strong class="dashboard-identity-item__value">{{ $user->max_students_per_quiz }}</strong>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </div>
        </section>

        <section class="dashboard-metrics mb-4 mb-lg-5">
            <article class="dashboard-metric-card">
                <span class="dashboard-metric-card__icon dashboard-metric-card__icon--blue">
                    <i class="fas fa-layer-group"></i>
                </span>
                <span class="dashboard-metric-card__label">{{ __('dashboard.total_quizzes') }}</span>
                <strong class="dashboard-metric-card__value">{{ $totalQuizCount }}</strong>
            </article>

            <article class="dashboard-metric-card">
                <span class="dashboard-metric-card__icon dashboard-metric-card__icon--green">
                    <i class="fas fa-circle-question"></i>
                </span>
                <span class="dashboard-metric-card__label">{{ __('dashboard.total_questions') }}</span>
                <strong class="dashboard-metric-card__value">{{ $totalQuestions }}</strong>
            </article>

            <article class="dashboard-metric-card">
                <span class="dashboard-metric-card__icon dashboard-metric-card__icon--amber">
                    <i class="fas fa-user-graduate"></i>
                </span>
                <span class="dashboard-metric-card__label">{{ __('dashboard.total_students') }}</span>
                <strong class="dashboard-metric-card__value">{{ $totalParticipants }}</strong>
            </article>
        </section>

        <section class="dashboard-section-card mb-4 mb-lg-5">
            <div class="dashboard-section-card__header">
                <div>
                    <h2 class="dashboard-section-card__title">{{ __('dashboard.my_active_quizzes') }}</h2>
                    <p class="dashboard-section-card__intro mb-0">{{ __('dashboard.quiz_collection_intro') }}</p>
                </div>

                <a href="{{ route('quizzes.index') }}" class="dashboard-inline-link">
                    {{ __('dashboard.go_to') }}
                    <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>

            @if ($quizzes->isNotEmpty())
                <div class="dashboard-quiz-list">
                    @foreach ($quizzes as $quiz)
                        <article class="dashboard-quiz-card">
                            <div class="dashboard-quiz-card__main">
                                <div class="dashboard-quiz-card__stamp">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>

                                <div class="dashboard-quiz-card__body">
                                    <h3 class="dashboard-quiz-card__title">{{ $quiz->title }}</h3>
                                    <div class="dashboard-quiz-card__meta">
                                        <span>
                                            <i class="far fa-calendar-plus me-1"></i>
                                            {{ __('dashboard.table_created_at') }}: {{ $quiz->created_at->format('d/m/Y') }}
                                        </span>
                                        <span>
                                            <i class="far fa-clock me-1"></i>
                                            {{ __('dashboard.table_updated_at') }}: {{ $quiz->updated_at->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-quiz-card__stats">
                                <div class="dashboard-quiz-stat">
                                    <span class="dashboard-quiz-stat__label">{{ __('dashboard.total_questions') }}</span>
                                    <strong class="dashboard-quiz-stat__value">{{ $quiz->question_count }}</strong>
                                </div>
                                <div class="dashboard-quiz-stat">
                                    <span class="dashboard-quiz-stat__label">{{ __('dashboard.table_students') }}</span>
                                    <strong class="dashboard-quiz-stat__value">{{ $quiz->student_count }}</strong>
                                </div>
                                <div class="dashboard-quiz-stat">
                                    <span class="dashboard-quiz-stat__label">{{ __('dashboard.table_completed') }}</span>
                                    <strong class="dashboard-quiz-stat__value">{{ $quiz->completed_attempts_count }}</strong>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="dashboard-empty-state">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('dashboard.no_active_quizzes') }}</h3>
                    <p class="dashboard-empty-state__text">{{ __('dashboard.quiz_collection_intro') }}</p>
                    <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-arrow-right me-2"></i>{{ __('navigation.my_quizzes') }}
                    </a>
                </div>
            @endif
        </section>

        <section class="dashboard-section-card mb-4 mb-lg-5">
            <div class="dashboard-section-card__header">
                <div>
                    <h2 class="dashboard-section-card__title">{{ __('dashboard.latest_updates') }}</h2>
                    <p class="dashboard-section-card__intro mb-0">{{ __('dashboard.updates_intro') }}</p>
                </div>

                @if ($user->role === 'admin')
                    <a href="{{ route('updates.create') }}" class="btn dashboard-btn dashboard-btn--soft">
                        <i class="fas fa-plus me-2"></i>{{ __('dashboard.add_update') }}
                    </a>
                @endif
            </div>

            @if ($updates->isNotEmpty())
                <div class="dashboard-updates-list">
                    @foreach ($updates as $update)
                        <article class="dashboard-update-item">
                            <div class="dashboard-update-item__content">
                                <div class="dashboard-update-item__main">
                                    <div class="dashboard-update-item__date">
                                        {{ $update->created_at->format('d/m/Y') }}
                                    </div>
                                    <p class="dashboard-update-item__text mb-0">{{ $update->description }}</p>
                                </div>

                                @if ($update->link)
                                    <a href="{{ $update->link }}" target="_blank" rel="noopener" class="dashboard-inline-link dashboard-update-item__link">
                                        {{ __('dashboard.updates_open_link') }}
                                        <i class="fas fa-up-right-from-square ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="dashboard-section-card__footer mt-4">
                    <a href="{{ route('updates.index') }}" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-ellipsis-h me-2"></i>{{ __('dashboard.view_all_updates') }}
                    </a>
                </div>
            @else
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('dashboard.updates_empty') }}</h3>
                </div>
            @endif
        </section>

        <section class="dashboard-section-card">
            <div class="dashboard-section-card__header">
                <div>
                    <h2 class="dashboard-section-card__title">{{ __('dashboard.resources_title') }}</h2>
                    <p class="dashboard-section-card__intro mb-0">{{ __('dashboard.resources_intro') }}</p>
                </div>
            </div>

            <div class="dashboard-resource-grid">
                <a href="https://labschool-gr.github.io/Exams/learn.html" class="dashboard-resource-link" target="_blank" rel="noopener">
                    <span class="dashboard-resource-link__icon">
                        <i class="fas fa-book-open"></i>
                    </span>
                    <span class="dashboard-resource-link__content">
                        <strong>{{ __('dashboard.manual_teacher') }}</strong><br>
                        <small>{{ __('dashboard.resource_manual_text') }}</small>
                    </span>
                    <i class="fas fa-arrow-right"></i>
                </a>

                <a href="{{ route('feedback.create') }}" class="dashboard-resource-link">
                    <span class="dashboard-resource-link__icon">
                        <i class="fas fa-comment-dots"></i>
                    </span>
                    <span class="dashboard-resource-link__content">
                        <strong>{{ __('dashboard.submit_feedback') }}</strong><br>
                        <small>{{ __('dashboard.resource_feedback_text') }}</small>
                    </span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
