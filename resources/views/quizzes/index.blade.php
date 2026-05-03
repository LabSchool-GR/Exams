@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-list-alt"></i>
                        {{ __('quizzes.index_title') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('dashboard.my_active_quizzes') }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.quiz_collection_intro') }}</p>
                </div>

                <div class="dashboard-form-actions">
                    @if ($canCreateQuiz ?? true)
                        <a href="{{ route('quizzes.create') }}" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-plus-circle me-2"></i>{{ __('quizzes.create_quiz') }}
                        </a>
                    @else
                        <form action="{{ route('quota_requests.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="resource_type" value="quizzes">
                            <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
                                <i class="fas fa-envelope me-2"></i>{{ __('quizzes.request_more_quizzes_button') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('error'))
                <div class="dashboard-status-card dashboard-status-card--danger mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('success'))
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(($exampleQuizzes ?? collect())->isNotEmpty())
                <div class="dashboard-collection-grid mb-5">
                    @foreach ($exampleQuizzes as $quiz)
                        <article class="dashboard-collection-card dashboard-collection-card--stacked-actions"
                                 style="background: linear-gradient(180deg, rgba(255, 247, 237, 0.98), rgba(255, 237, 213, 0.94)); border-color: rgba(194, 120, 3, 0.18);">
                            <div class="dashboard-collection-card__body">
                                <div class="dashboard-collection-card__main">
                                    <div class="dashboard-collection-card__icon">
                                        <i class="fas fa-compass"></i>
                                    </div>

                                    <div>
                                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                            <h2 class="dashboard-collection-card__title mb-0">{{ $quiz->title }}</h2>
                                            <span class="dashboard-collection-pill"
                                                  style="background: rgba(255, 255, 255, 0.72); color: #b45309; border: 1px solid rgba(194, 120, 3, 0.18);">
                                                <i class="fas fa-lock"></i>{{ __('quizzes.platform_example_badge') }}
                                            </span>
                                        </div>

                                        <p class="dashboard-collection-card__text">
                                            {{ $quiz->description ?: __('quizzes.platform_example_fallback_description') }}
                                        </p>

                                        <p class="dashboard-collection-card__text mb-0">
                                            {{ __('quizzes.platform_example_note') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="dashboard-collection-meta">
                                    <span class="dashboard-collection-pill">
                                        <i class="fas fa-folder-open"></i>{{ $quiz->category->name }}
                                    </span>
                                    <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                        <i class="fas fa-eye"></i>{{ __('quizzes.platform_example_read_only') }}
                                    </span>
                                </div>
                            </div>

                            <div class="dashboard-collection-card__actions dashboard-collection-card__actions--compact">
                                @if (auth()->user()->isAdmin())
                                    @if ($quiz->publicAccessUrl())
                                        <a href="{{ $quiz->publicAccessUrl() }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                            <i class="fas fa-play-circle me-2"></i>{{ __('quizzes.try_as_guest') }}
                                        </a>
                                    @endif
                                    <a href="{{ route('quizzes.edit', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                        <i class="fas fa-edit me-2"></i>{{ __('quizzes.edit') }}
                                    </a>
                                    <a href="{{ route('quizzes.questions.index', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                        <i class="fas fa-question-circle me-2"></i>{{ __('quizzes.questions') }}
                                    </a>
                                    <a href="{{ route('quizzes.printable_pdf', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                        <i class="fas fa-print me-2"></i>{{ __('quizzes.export_pdf') }}
                                    </a>
                                    <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" class="dashboard-inline-form" data-confirm-submit="{{ __('quizzes.confirm_delete') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dashboard-danger-button dashboard-danger-button--compact">
                                            <i class="fas fa-trash-alt me-2"></i>{{ __('quizzes.delete') }}
                                        </button>
                                    </form>
                                @else
                                    @if ($quiz->publicAccessUrl())
                                        <a href="{{ $quiz->publicAccessUrl() }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                            <i class="fas fa-play-circle me-2"></i>{{ __('quizzes.try_as_guest') }}
                                        </a>
                                    @endif
                                    <a href="{{ route('quizzes.printable_pdf', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                        <i class="fas fa-file-lines me-2"></i>{{ __('quizzes.preview_example_pdf') }}
                                    </a>
                                    <form action="{{ route('quizzes.duplicate', $quiz) }}" method="POST" class="dashboard-inline-form">
                                        @csrf
                                        <button type="submit" class="dashboard-primary-button dashboard-secondary-button--compact">
                                            <i class="fas fa-copy me-2"></i>{{ __('quizzes.copy_as_new_quiz') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($quizzes->isEmpty())
                <div class="dashboard-empty-state">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">
                        {{ ($exampleQuizzes ?? collect())->isNotEmpty() ? __('quizzes.no_personal_quizzes') : __('quizzes.no_quizzes') }}
                    </h3>
                    @if ($canCreateQuiz ?? true)
                        <a href="{{ route('quizzes.create') }}" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-plus-circle me-2"></i>{{ __('quizzes.create_quiz') }}
                        </a>
                    @endif
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach ($quizzes as $quiz)
                        <article class="dashboard-collection-card dashboard-collection-card--stacked-actions">
                            <div class="dashboard-collection-card__body">
                                <div class="dashboard-collection-card__main">
                                    <div class="dashboard-collection-card__icon">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>

                                    <div>
                                        <h2 class="dashboard-collection-card__title">{{ $quiz->title }}</h2>
                                        <p class="dashboard-collection-card__text">
                                            {{ $quiz->description ?: __('dashboard.quiz_collection_intro') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="dashboard-collection-meta">
                                    <span class="dashboard-collection-pill">
                                        <i class="fas fa-folder-open"></i>{{ $quiz->category->name }}
                                    </span>
                                    <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                        <i class="fas fa-key"></i>{{ $quiz->quiz_code }}
                                    </span>
                                    <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                        <i class="fas fa-calendar-alt"></i>{{ $quiz->created_at->format('d/m/Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="dashboard-collection-card__actions dashboard-collection-card__actions--compact">
                                <a href="{{ route('quizzes.edit', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-edit me-2"></i>{{ __('quizzes.edit') }}
                                </a>
                                <a href="{{ route('quizzes.questions.index', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-question-circle me-2"></i>{{ __('quizzes.questions') }}
                                </a>
                                <a href="{{ route('quiz_attempts.register_students', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-user-graduate me-2"></i>{{ __('quizzes.students') }}
                                </a>
                                <a href="{{ route('quizzes.quiz_attempts.index', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-chart-bar me-2"></i>{{ __('quizzes.attempts') }}
                                </a>
                                <a href="{{ route('quizzes.printable_pdf', $quiz) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-print me-2"></i>{{ __('quizzes.export_pdf') }}
                                </a>
                                <form action="{{ route('quizzes.duplicate', $quiz) }}" method="POST" class="dashboard-inline-form">
                                    @csrf
                                    <button type="submit" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                        <i class="fas fa-copy me-2"></i>{{ __('quizzes.copy_as_new_quiz') }}
                                    </button>
                                </form>
                                <form action="{{ route('quizzes.destroy', $quiz) }}" method="POST" class="dashboard-inline-form" data-confirm-submit="{{ __('quizzes.confirm_delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dashboard-danger-button dashboard-danger-button--compact">
                                        <i class="fas fa-trash-alt me-2"></i>{{ __('quizzes.delete') }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $quizzes->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
