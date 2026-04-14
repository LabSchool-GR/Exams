@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-question-circle"></i>
                        {{ __('quizzes.questions_of_quiz') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $quiz->title }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.quiz_collection_intro') }}</p>
                </div>

                <div class="dashboard-page-header__actions">
                    @if ($canAddQuestion ?? true)
                        <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--ghost dashboard-btn--header-compact">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.collection') }}
                        </a>
                        <a href="{{ route('quizzes.questions.create', $quiz) }}" class="btn dashboard-btn dashboard-btn--primary dashboard-btn--header-compact">
                            <i class="fas fa-plus-circle me-2"></i>{{ __('quizzes.add_question') }}
                        </a>
                    @elseif ($isContentLocked ?? false)
                        <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--ghost dashboard-btn--header-compact">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.collection') }}
                        </a>
                    @else
                        <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--ghost dashboard-btn--header-compact">
                            <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.collection') }}
                        </a>
                        <form action="{{ route('quota_requests.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="resource_type" value="questions">
                            <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                            <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
                                <i class="fas fa-envelope me-2"></i>{{ __('quizzes.request_more_questions_button') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="dashboard-status-card dashboard-status-card--danger mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if($isContentLocked ?? false)
                <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                    <i class="fas fa-lock"></i>
                    <div>{{ __('controllers.quiz_content_locked') }}</div>
                </div>
            @endif

            @if($questions->isEmpty())
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-circle-question"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('quizzes.no_questions_found') }}</h3>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach ($questions as $index => $question)
                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__main">
                                <div class="dashboard-collection-card__icon">
                                    <i class="fas fa-circle-question"></i>
                                </div>

                                <div>
                                    <h2 class="dashboard-collection-card__title">{{ __('quizzes.question') }} {{ $index + 1 }}</h2>
                                    <p class="dashboard-collection-card__text">{{ $question->text }}</p>

                                    <div class="dashboard-collection-meta">
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-check-double"></i>{{ __('quizzes.correct_answers_count') }}: {{ $question->correct_answers_count }}
                                        </span>
                                        @if(!is_null($question->order))
                                            <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                                <i class="fas fa-sort-numeric-up"></i>{{ __('quizzes_cards.order') }}: {{ $question->order }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-collection-actions">
                                @if(!($isContentLocked ?? false))
                                    <a href="{{ route('quizzes.questions.edit', [$quiz, $question]) }}" class="btn dashboard-btn dashboard-btn--ghost">
                                        <i class="fas fa-edit me-2"></i>{{ __('quizzes.edit_question') }}
                                    </a>

                                    <form action="{{ route('quizzes.questions.destroy', [$quiz, $question]) }}" method="POST" data-confirm-submit="{{ __('quizzes.confirm_delete_question') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn dashboard-btn btn-danger">
                                            <i class="fas fa-trash-alt me-2"></i>{{ __('quizzes.delete_question') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="dashboard-section-card mt-4">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-file-import"></i>
                        {{ __('quizzes.import_questions') }}
                    </span>
                    <h2 class="dashboard-page-header__title">{{ __('quizzes.import_questions') }}</h2>
                    <p class="dashboard-page-header__text">{{ __('quizzes.csv_format_hint') }}: <span class="dashboard-code-inline">text, answer_1, answer_2, ..., correct_answers</span></p>
                </div>
            </div>

            <form action="{{ route('quizzes.questions.import', $quiz) }}" method="POST" enctype="multipart/form-data" id="questions-import-form" class="dashboard-form-stack" data-question-import-form data-max-lines="20" data-expected-header-prefix="text,answer_1,answer_2" data-empty-file-message="{{ __('ui.csv_empty_file') }}" data-read-error-message="{{ __('ui.csv_read_error') }}" data-too-many-rows-message="{{ __('ui.question_csv_too_many_rows') }}" data-invalid-headers-message="{{ __('ui.question_csv_invalid_headers') }}" data-empty-question-message="{{ __('ui.question_csv_empty_text') }}">
                @csrf

                <div class="dashboard-form-group">
                    <label for="questions_csv" class="dashboard-form-label">
                        <i class="fas fa-file-csv text-muted"></i>{{ __('quizzes.select_csv_file') }}
                    </label>
                    <input type="file" name="questions_csv" id="questions_csv" class="form-control dashboard-form-control" accept=".csv" data-question-import-input required @if($isContentLocked ?? false) disabled @endif>
                    <div class="dashboard-form-help">
                        {{ __('quizzes.csv_format_hint') }}: <span class="dashboard-code-inline">text, answer_1, answer_2, ..., correct_answers</span> - max 20 rows, UTF-8 encoded
                    </div>
                    <div class="dashboard-form-help">
                        <span class="dashboard-code-inline">correct_answers</span> accepts answer numbers like <span class="dashboard-code-inline">1</span> or <span class="dashboard-code-inline">1,3</span>
                    </div>
                    <div class="dashboard-form-help">
                        <a href="{{ asset('storage/docs/questions_template.csv') }}" target="_blank" rel="noopener" class="dashboard-inline-link">
                            <i class="fas fa-file-csv me-1"></i>{{ __('quizzes.download_csv_template') }}
                        </a>
                    </div>
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--primary" @if(!($canAddQuestion ?? true) || ($isContentLocked ?? false)) disabled @endif>
                        <i class="fas fa-upload me-2"></i>{{ __('quizzes.import') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection
