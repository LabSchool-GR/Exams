@extends('layouts.quiz_guest')

@section('content')
<div class="quiz-display-shell quiz-display-shell--controller">
    <div
        class="quiz-display-controller"
        data-quiz-display-controller
        data-state-url="{{ $stateUrl }}"
        data-answer-url="{{ $answerUrl }}"
        data-navigate-url="{{ $navigateUrl }}"
        data-submit-url="{{ $submitUrl }}"
        data-poll-interval="{{ $pollIntervalMs }}"
        data-csrf-token="{{ csrf_token() }}"
        data-expired-status-label="{{ __('display.status_expired') }}"
        data-expired-message="{{ __('display.screen_expired') }}"
    >
        <div class="quiz-display-controller__header">
            <div class="quiz-display-controller__header-main">
                <span class="quiz-display-panel__eyebrow">
                    <i class="fas fa-mobile-screen-button"></i>{{ __('display.controller_label') }}
                </span>
                <h1 class="quiz-display-panel__title">{{ $quiz->title }}</h1>
            </div>

            <div class="quiz-display-controller__participant-card">
                <span class="quiz-display-controller__participant-label">{{ __('display.participant_label') }}</span>
                <strong class="quiz-display-controller__participant-name" data-controller-student>{{ $displaySession->student?->student_name }}</strong>
            </div>
        </div>

        <div class="quiz-display-controller__meta">
            <div class="quiz-display-controller__meta-item">
                <span>{{ __('display.progress_label') }}</span>
                <strong data-controller-progress>1 / 1</strong>
            </div>
            <div class="quiz-display-controller__meta-item">
                <span>{{ __('display.connection_label') }}</span>
                <strong data-controller-status>{{ __('display.controller_ready') }}</strong>
            </div>
        </div>

        <div class="quiz-display-controller__body">
            <div class="quiz-display-placeholder" data-controller-placeholder>
                <div class="quiz-display-placeholder__icon">
                    <i class="fas fa-spinner"></i>
                </div>
                <h2 class="quiz-display-placeholder__title">{{ __('display.loading_title') }}</h2>
                <p class="quiz-display-placeholder__text">{{ __('display.loading_text') }}</p>
            </div>

            <article class="quiz-display-question d-none" data-controller-question-card>
                <header class="quiz-display-question__header">
                    <p class="quiz-display-question__progress" data-controller-question-label></p>
                    <h2 class="quiz-display-question__title" data-controller-question-text></h2>
                    <p class="quiz-display-controller__instruction" data-controller-instruction></p>
                </header>

                <div class="quiz-display-question__media d-none" data-controller-image-shell>
                    <img src="" alt="{{ __('display.question_image_alt') }}" data-controller-image>
                </div>

                <div class="quiz-display-answers" data-controller-answers></div>

                <div class="quiz-display-selection">
                    <span>{{ __('display.live_selection_label') }}</span>
                    <strong data-controller-selection>{{ __('display.no_answer_selected') }}</strong>
                </div>

                <div class="quiz-display-controller__actions">
                    <p class="quiz-display-controller__helper d-none" data-controller-action-helper></p>
                    <button type="button" class="btn dashboard-btn dashboard-btn--ghost" data-controller-previous>
                        <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.previous_question') }}
                    </button>
                    <button type="button" class="btn dashboard-btn dashboard-btn--ghost" data-controller-next>
                        <i class="fas fa-arrow-right me-2"></i>{{ __('quizzes.next_question') }}
                    </button>
                    <button type="button" class="btn dashboard-btn dashboard-btn--primary" data-controller-submit>
                        <i class="fas fa-flag-checkered me-2"></i>{{ __('join.submit_quiz') }}
                    </button>
                </div>
            </article>

            <article class="quiz-display-result d-none" data-controller-result-card>
                <div class="quiz-display-result__icon">
                    <i class="fas fa-circle-check"></i>
                </div>
                <h2 class="quiz-display-result__title">{{ __('display.controller_completed_title') }}</h2>
                <p class="quiz-display-panel__subtitle mb-2" data-controller-result-note></p>
                <p class="quiz-display-result__text" data-controller-result-text></p>
                <div class="dashboard-form-actions dashboard-form-actions--center mt-4 quiz-display-controller__result-actions">
                    <a href="#" class="btn dashboard-btn dashboard-btn--primary d-none" data-controller-result-pdf target="_blank" rel="noopener">
                        <i class="fas fa-file-pdf me-2"></i>{{ __('join.download_pdf') }}
                    </a>
                    @auth
                        <a href="{{ route('feedback.create') }}" class="btn dashboard-btn dashboard-btn--ghost" target="_blank" rel="noopener">
                            <i class="fas fa-comment-dots me-2"></i>{{ __('dashboard.submit_feedback') }}
                        </a>
                    @endauth
                </div>
            </article>
        </div>
    </div>
</div>
@endsection
