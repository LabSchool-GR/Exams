@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-chart-pie"></i>
                        {{ __('quizzes.question_stats') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $quiz->title }}</h1>
                </div>
            </div>

            @if (empty($stats))
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-chart-column"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('quizzes.no_attempts') }}</h3>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach ($stats as $index => $row)
                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__main">
                                <div class="dashboard-collection-card__icon">
                                    <i class="fas fa-circle-question"></i>
                                </div>
                                <div>
                                    <h2 class="dashboard-collection-card__title">{{ __('quizzes.question_stat') }} {{ $index + 1 }}</h2>
                                    <p class="dashboard-collection-card__text">{{ $row['question'] }}</p>
                                    <div class="dashboard-collection-meta">
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-check"></i>{{ __('quizzes.correct_stats') }}: {{ $row['correct'] }}
                                        </span>
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-xmark"></i>{{ __('quizzes.incorrect_stats') }}: {{ $row['wrong'] }}
                                        </span>
                                        <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                            <i class="fas fa-minus"></i>{{ __('quizzes.unanswered_stats') }}: {{ $row['unanswered'] }}
                                        </span>
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-percent"></i>{{ __('quizzes.score_stats') }}: {{ $row['success_rate'] }}%
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="dashboard-form-actions mt-4">
                <a href="{{ route('quizzes.quiz_attempts.index', $quiz) }}" class="btn dashboard-btn dashboard-btn--ghost">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.back_to_list_stats') }}
                </a>
                <a href="{{ route('quiz_attempts.question_stats_export', $quiz) }}" class="btn dashboard-btn dashboard-btn--primary">
                    <i class="fas fa-file-excel me-2"></i>{{ __('quizzes.export_excel') }}
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
