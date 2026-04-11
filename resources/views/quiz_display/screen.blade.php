@extends('layouts.quiz_guest')

@section('body_class', 'quiz-display-page quiz-display-page--screen')

@section('content')
<div class="quiz-display-shell quiz-display-shell--screen">
    <div
        class="quiz-display-panel"
        data-quiz-display-screen
        data-state-url="{{ $stateUrl }}"
        data-poll-interval="{{ $pollIntervalMs }}"
        data-expired-status-label="{{ __('display.status_expired') }}"
        data-expired-message="{{ __('display.screen_expired') }}"
    >
        <div class="quiz-display-panel__header">
            <div>
                <span class="quiz-display-panel__eyebrow">
                    <i class="fas fa-tv"></i>{{ __('display.screen_label') }}
                </span>
                <h1 class="quiz-display-panel__title">{{ $quiz->title }}</h1>
                <p class="quiz-display-panel__subtitle">{{ __('display.screen_intro') }}</p>
            </div>
            <div class="quiz-display-panel__status" data-display-status-badge>
                {{ __('display.status_waiting') }}
            </div>
        </div>

        <div class="quiz-display-grid" data-display-grid>
            <section class="quiz-display-stage">
                <div class="quiz-display-stage__meta">
                    <div class="quiz-display-stage__meta-item">
                        <span>{{ __('display.participant_label') }}</span>
                        <strong data-display-student>{{ $displaySession->student?->student_name }}</strong>
                    </div>
                    <div class="quiz-display-stage__meta-item">
                        <span>{{ __('display.progress_label') }}</span>
                        <strong data-display-progress>1 / 1</strong>
                    </div>
                    <div class="quiz-display-stage__meta-item">
                        <span>{{ __('display.timer_label') }}</span>
                        <strong data-display-timer>{{ __('display.timer_waiting') }}</strong>
                    </div>
                </div>

                <div class="quiz-display-stage__body">
                    <div class="quiz-display-placeholder" data-display-placeholder>
                        <div class="quiz-display-placeholder__icon">
                            <i class="fas fa-mobile-screen-button"></i>
                        </div>
                        <h2 class="quiz-display-placeholder__title">{{ __('display.screen_waiting') }}</h2>
                        <p class="quiz-display-placeholder__text quiz-display-placeholder__text--lead">{{ __('display.screen_waiting_cta') }}</p>
                        <div class="quiz-display-screen-steps" aria-label="{{ __('display.screen_waiting_steps_title') }}">
                            <div class="quiz-display-screen-step">
                                <span class="quiz-display-screen-step__number">1</span>
                                <div>
                                    <strong>{{ __('display.screen_waiting_step_1_title') }}</strong>
                                    <p>{{ __('display.screen_waiting_step_1_text') }}</p>
                                </div>
                            </div>
                            <div class="quiz-display-screen-step">
                                <span class="quiz-display-screen-step__number">2</span>
                                <div>
                                    <strong>{{ __('display.screen_waiting_step_2_title') }}</strong>
                                    <p>{{ __('display.screen_waiting_step_2_text') }}</p>
                                </div>
                            </div>
                            <div class="quiz-display-screen-step">
                                <span class="quiz-display-screen-step__number">3</span>
                                <div>
                                    <strong>{{ __('display.screen_waiting_step_3_title') }}</strong>
                                    <p>{{ __('display.screen_waiting_step_3_text') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <article class="quiz-display-question d-none" data-display-question-card>
                        <header class="quiz-display-question__header">
                            <p class="quiz-display-question__progress" data-display-question-label></p>
                            <h2 class="quiz-display-question__title" data-display-question-text></h2>
                        </header>

                        <div class="quiz-display-question__media d-none" data-display-image-shell>
                            <img src="" alt="{{ __('display.question_image_alt') }}" data-display-image>
                        </div>

                        <div class="quiz-display-selection">
                            <span>{{ __('display.live_selection_label') }}</span>
                            <strong data-display-selection>{{ __('display.no_answer_selected') }}</strong>
                        </div>

                        <div class="quiz-display-answers" data-display-answers></div>
                    </article>

                    <article class="quiz-display-result d-none" data-display-result-card>
                        <div class="quiz-display-result__icon">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <h2 class="quiz-display-result__title">{{ __('display.screen_completed') }}</h2>
                        <p class="quiz-display-panel__subtitle mb-2" data-display-result-note></p>
                        <p class="quiz-display-result__text" data-display-result-text></p>
                        <div class="dashboard-form-actions dashboard-form-actions--center mt-4">
                            <a href="#" class="btn dashboard-btn dashboard-btn--primary d-none" data-display-result-pdf target="_blank" rel="noopener">
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
            </section>

            <aside class="quiz-display-sidebar" data-display-sidebar>
                <div class="quiz-display-sidebar__card">
                    <span class="quiz-display-sidebar__eyebrow">{{ __('display.qr_title') }}</span>
                    <h2>{{ __('display.screen_waiting_qr_title') }}</h2>
                    <p class="quiz-display-sidebar__lead">{{ __('display.screen_waiting_qr_text') }}</p>
                    <div class="quiz-display-sidebar__qr">
                        <img src="data:image/svg+xml;base64,{{ $qrSvg }}" alt="{{ __('display.qr_alt') }}">
                    </div>

                    <input type="text" id="display-pair-link" class="form-control dashboard-form-control mt-3" value="{{ $pairUrl }}" readonly>
                    <button
                        type="button"
                        class="btn dashboard-btn dashboard-btn--ghost mt-3"
                        data-copy-target="display-pair-link"
                        data-copy-success="{{ __('ui.copy_link_success') }}"
                        data-copy-error="{{ __('ui.copy_link_failed') }}"
                    >
                        <i class="fas fa-copy me-2"></i>{{ __('display.copy_mobile_link') }}
                    </button>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection
