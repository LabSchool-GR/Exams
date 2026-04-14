@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-file-alt"></i>
                        {{ __('quizzes.title_attempts') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $quiz->title }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.quiz_collection_intro') }}</p>
                </div>

                <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--ghost">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.collection') }}
                </a>
            </div>

            @if(session('success'))
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            <form method="GET" class="dashboard-form-panel mb-4">
                <div class="dashboard-form-grid">
                    <div class="dashboard-form-group">
                        <label for="search" class="dashboard-form-label">
                            <i class="fas fa-search text-muted"></i>{{ __('quizzes.search_placeholder') }}
                        </label>
                        <input type="text" name="search" id="search" class="form-control dashboard-form-control" value="{{ request('search') }}">
                    </div>

                    <div class="dashboard-form-group">
                        <label for="per_page" class="dashboard-form-label">
                            <i class="fas fa-list text-muted"></i>{{ __('quizzes.per_page') }}
                        </label>
                        <select name="per_page" id="per_page" class="form-select dashboard-form-control">
                            @foreach ([10, 20, 50, 100] as $size)
                                <option value="{{ $size }}" {{ request('per_page', 10) == $size ? 'selected' : '' }}>
                                    {{ $size }} {{ __('quizzes.per_page') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="dashboard-form-actions">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                        <i class="fas fa-search me-2"></i>{{ __('quizzes.filter') }}
                    </button>
                    <a href="{{ route('quiz_attempts.export_excel', $quiz->id) }}" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-file-excel me-2"></i>{{ __('quizzes.export_excel') }}
                    </a>
                </div>
            </form>

            @if($attempts->isEmpty())
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-clipboard-question"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('quizzes.no_attempts') }}</h3>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @php $studentAttemptsCount = []; @endphp
                    @foreach ($attempts as $attempt)
                        @php
                            $code = $attempt->student_code;
                            $attemptKey = $attempt->quiz_student_id ? 'student:' . $attempt->quiz_student_id : 'code:' . $code;
                            $studentAttemptsCount[$attemptKey] = ($studentAttemptsCount[$attemptKey] ?? 0) + 1;
                        @endphp
                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__main">
                                <div class="dashboard-collection-card__icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <div>
                                    <h2 class="dashboard-collection-card__title">{{ $attempt->student_name }}</h2>
                                    <div class="dashboard-collection-meta">
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-hashtag"></i>{{ $studentAttemptsCount[$attemptKey] }}
                                        </span>
                                        <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                            <i class="fas fa-key"></i>{{ $code }}
                                        </span>
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-percent"></i>{{ $attempt->score }}%
                                        </span>
                                        <span class="dashboard-collection-pill {{ $attempt->submitted_at ? '' : 'dashboard-collection-pill--muted' }}">
                                            <i class="fas {{ $attempt->submitted_at ? 'fa-check-circle' : 'fa-hourglass-half' }}"></i>
                                            {{ $attempt->submitted_at ? __('quizzes.completed') : __('quizzes.in_progress') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-collection-actions">
                                @if($attempt->submitted_at)
                                    <a href="{{ route('quiz_attempts.download_pdf', [$quiz, $attempt]) }}" class="btn dashboard-btn dashboard-btn--ghost">
                                        <i class="fas fa-file-pdf me-2"></i>PDF
                                    </a>
                                @endif
                                @if ($attempt->score >= $attempt->quiz->pass_percentage)
                                    <a href="{{ route('quiz_attempts.certificate', $attempt) }}" class="btn dashboard-btn dashboard-btn--primary" target="_blank">
                                        <i class="fas fa-award me-2"></i>{{ __('quizzes.download_certificate') }}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="dashboard-form-actions mt-4">
                    <a href="{{ route('quiz_attempts.question_stats', $quiz) }}" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-chart-bar me-2"></i>{{ __('quizzes.question_stats_go') }}
                    </a>
                </div>

                <div class="mt-4 d-flex justify-content-center">
                    {{ $attempts->links('vendor.pagination.bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
