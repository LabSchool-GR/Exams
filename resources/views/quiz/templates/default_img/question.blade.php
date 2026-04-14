@extends('layouts.quiz_guest')

@section('content')
<style>
:root {
    --quiz-bg: #edf3f7;
    --quiz-paper: rgba(255, 255, 255, 0.95);
    --quiz-ink: #17324d;
    --quiz-muted: #5e7389;
    --quiz-accent: #1f7a8c;
    --quiz-accent-soft: rgba(31, 122, 140, 0.12);
    --quiz-border: rgba(23, 50, 77, 0.12);
    --quiz-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    --quiz-success: #177245;
    --quiz-warning: #b7791f;
}

body {
    min-height: 100vh;
    background-image: url('{{ asset('storage/bg-quiz.jpg') }}');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
}

.overlay {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.68);
    z-index: 1;
}

.screen-shell {
    position: relative;
    z-index: 2;
    min-height: 100vh;
    padding: 1rem 0.85rem;
}

.exam-card {
    width: 100%;
    max-width: 760px;
    border-radius: 1.1rem;
    border: 1px solid var(--quiz-border);
    background: rgba(255, 255, 255, 0.86);
    box-shadow: 0 20px 42px rgba(15, 23, 42, 0.15);
    backdrop-filter: blur(6px);
    overflow: hidden;
}

.exam-card__inner {
    padding: 1.2rem 1.25rem;
}

.hero-bar {
    display: grid;
    justify-items: center;
    gap: 0.15rem;
    margin-bottom: 0.65rem;
}

.quiz-context-title {
    margin: 0;
    text-align: center;
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--quiz-muted);
}

.question-progress-text {
    margin: 0;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 700;
    color: var(--quiz-muted);
}

.question-stage {
    display: grid;
    gap: 0.95rem;
}

.question-panel--headline {
    grid-column: 1 / -1;
}

.question-content-column {
    display: grid;
    gap: 0.85rem;
}

.question-answer-form {
    display: grid;
    gap: 0.75rem;
}

.question-image-shell--inline {
    display: block;
}

.question-media-column {
    display: none;
}

.question-panel {
    padding: 0.8rem 0.9rem;
    border-radius: 1rem;
    background: linear-gradient(180deg, rgba(255, 255, 255, 0.82), rgba(245, 249, 252, 0.9));
    border: 1px solid rgba(23, 50, 77, 0.08);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
}

.question-panel--headline {
    padding: 0.1rem 0 0.15rem;
    border: 0;
    background: transparent;
    box-shadow: none;
    display: flex;
    justify-content: center;
}

.question-title {
    margin: 0;
    width: min(100%, 42rem);
    padding: 0.8rem 1rem;
    border-radius: 1rem;
    font-size: 1.1rem;
    line-height: 1.45;
    font-weight: 800;
    color: var(--quiz-ink);
    white-space: pre-line;
    text-align: left;
    background: linear-gradient(180deg, rgba(220, 243, 229, 0.96), rgba(236, 250, 241, 0.92));
    border: 1px solid rgba(23, 114, 69, 0.14);
    box-shadow: 0 12px 28px rgba(23, 114, 69, 0.08);
}

.question-image-shell {
    padding: 0.55rem;
    border-radius: 0.95rem;
    background: rgba(255, 255, 255, 0.88);
    border: 1px solid rgba(23, 50, 77, 0.08);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.7),
        0 16px 32px rgba(15, 23, 42, 0.08);
    height: 100%;
}

.question-image {
    display: block;
    width: 100%;
    max-height: 320px;
    object-fit: contain;
    border-radius: 0.95rem;
    background: #f8fbfd;
}

.answer-list {
    display: grid;
    gap: 0.55rem;
}

.answer-option {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    width: 100%;
    padding: 0.72rem 0.85rem;
    border-radius: 0.95rem;
    border: 1px solid rgba(23, 50, 77, 0.1);
    background: rgba(255, 255, 255, 0.9);
    box-shadow: 0 10px 26px rgba(15, 23, 42, 0.06);
    transition:
        transform 0.16s ease,
        box-shadow 0.16s ease,
        border-color 0.16s ease,
        background-color 0.16s ease;
}

.answer-option:hover {
    cursor: pointer;
    transform: translateY(-1px);
    border-color: rgba(31, 122, 140, 0.26);
    box-shadow: 0 14px 28px rgba(31, 122, 140, 0.12);
}

.answer-option:has(input:checked) {
    background: linear-gradient(135deg, rgba(31, 122, 140, 0.1), rgba(66, 133, 244, 0.08));
    border-color: rgba(31, 122, 140, 0.34);
    box-shadow: 0 18px 30px rgba(31, 122, 140, 0.14);
}

.answer-input {
    width: 1.15rem;
    height: 1.15rem;
    flex-shrink: 0;
    accent-color: var(--quiz-accent);
}

.answer-copy {
    flex: 1;
    color: #243b53;
    line-height: 1.45;
}

.answer-option input:checked ~ .answer-copy {
    color: var(--quiz-ink);
    font-weight: 600;
}

.selection-state {
    margin-top: 0.7rem;
    padding: 0.65rem 0.8rem;
    border-radius: 0.95rem;
    background: rgba(255, 255, 255, 0.78);
    border: 1px solid rgba(23, 50, 77, 0.08);
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

#selection-info,
#selected-answer {
    margin: 0;
    font-size: 0.86rem;
    min-height: 0;
    line-height: 1.35;
}

#selection-info {
    color: var(--quiz-muted);
    flex: 0 1 auto;
}

#selected-answer {
    color: var(--quiz-ink);
    flex: 0 1 auto;
    text-align: right;
    font-weight: 600;
}

.actions-grid {
    display: grid;
    gap: 0.65rem;
    margin-top: 0.8rem;
}

.question-actions-row {
    display: grid;
    gap: 0.65rem;
    margin-top: 0.8rem;
}

.question-stage__actions {
    grid-column: 1 / -1;
}

.question-stage__selection {
    grid-column: 1 / -1;
}

.question-actions-row > * {
    margin: 0;
}

.question-actions-row--single {
    grid-template-columns: 1fr;
}

.btn-submit,
.btn-skip {
    border-radius: 999px;
    padding: 0.72rem 1rem;
    font-weight: 700;
    border: 0;
    width: 100%;
}

.btn-submit {
    background: linear-gradient(135deg, #1f7a8c, #2a9d8f);
    color: #fff;
    box-shadow: 0 18px 30px rgba(31, 122, 140, 0.22);
}

.btn-submit:hover,
.btn-submit:focus {
    color: #fff;
}

.btn-submit.btn-success {
    background: linear-gradient(135deg, #177245, #22a55b);
}

.btn-skip {
    background: rgba(183, 121, 31, 0.12);
    color: var(--quiz-warning);
    border: 1px solid rgba(183, 121, 31, 0.24);
}

.btn-skip:hover,
.btn-skip:focus {
    color: #8a5a12;
}

.timer-block {
    margin-top: 0.85rem;
}

.progress {
    height: 0.8rem;
    border-radius: 999px;
    background: rgba(23, 50, 77, 0.08);
}

.progress-bar {
    background: linear-gradient(90deg, #1f7a8c, #2a9d8f);
}

.progress-bar--danger {
    background: linear-gradient(90deg, #d64545, #f97316);
}

#time-remaining {
    margin: 0.5rem 0 0;
    color: var(--quiz-ink);
    font-weight: 700;
    text-align: center;
    font-size: 0.92rem;
}

@media (max-height: 820px) {
    .question-image {
        max-height: 220px;
    }

    .answer-list {
        gap: 0.5rem;
    }
}

@media (max-height: 720px) {
    .question-title {
        font-size: 1rem;
    }

    .quiz-context-title {
        font-size: 0.76rem;
    }

    .question-image {
        max-height: 135px;
    }

    .answer-copy,
    #selection-info,
    #selected-answer {
        font-size: 0.82rem;
    }
}

#time-remaining.text-danger {
    color: #b42318 !important;
}

.fade-in {
    opacity: 0;
    animation: riseIn 0.55s ease-out forwards;
}

@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(14px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 576px) {
    .screen-shell {
        padding: 0.85rem 0.65rem;
    }

    .exam-card {
        border-radius: 1rem;
    }

    .exam-card__inner {
        padding: 1rem;
    }

    .question-title {
        font-size: 0.98rem;
    }

    .selection-state {
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 0.25rem;
        justify-content: flex-start;
    }

    #selection-info,
    #selected-answer {
        width: 100%;
        text-align: left;
    }

    .answer-option {
        padding: 0.72rem 0.75rem;
    }
}

@media (min-width: 768px) {
    .question-image-shell--inline {
        display: none;
    }

    .question-stage--with-media {
        grid-template-columns: minmax(240px, 290px) minmax(0, 1fr);
        grid-template-areas:
            "headline headline"
            "media content"
            "selection selection"
            "actions actions";
        align-items: start;
    }

    .question-stage--with-media .question-panel--headline {
        grid-area: headline;
    }

    .question-stage--with-media .question-content-column {
        grid-area: content;
    }

    .question-stage--with-media .question-media-column {
        display: block;
        grid-area: media;
        position: sticky;
        top: 1rem;
    }

    .question-stage--with-media .question-image {
        max-height: 420px;
    }

    .question-stage--with-media .question-stage__selection {
        grid-area: selection;
    }

    .question-stage--with-media .question-stage__actions {
        grid-area: actions;
    }

    .question-actions-row {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: stretch;
    }

    .question-actions-row--single {
        grid-template-columns: 1fr;
    }
}
</style>

@php
    $correctCount = $question->answers->where('is_correct', true)->count();
    $instructionText = trans_choice('join.select_instruction', $correctCount, ['count' => $correctCount]);
    $showLearningFeedback = $showLearningFeedback ?? false;
    $isLearningMode = $isLearningMode ?? false;
    $safeTimeRemaining = max(0, (int) $timeRemaining);
    $formattedTimeRemaining = sprintf('%02d:%02d', intdiv($safeTimeRemaining, 60), $safeTimeRemaining % 60);
@endphp

@if(!$allowDisplay)
    <div class="overlay"></div>
    <div class="container screen-shell d-flex justify-content-center align-items-start">
        <div class="exam-card"
             data-quiz-question-runtime
             data-correct-count="{{ $correctCount }}"
             data-instruction-text="{{ $instructionText }}"
             data-selected-prefix="{{ __('join.selected') }}"
             data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}"
             data-attempt-id="{{ session('attempt_id') }}"
             data-force-submit-url="{{ route('quiz.force_submit') }}"
             data-csrf-token="{{ csrf_token() }}"
             data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}"
             data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}"
             data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}"
             data-server-now="{{ now()->timestamp }}"
             data-time-limit="{{ $quiz->time_limit }}"
             data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}"
             data-final-fallback-target=".quiz-runtime-fallback-target">
            <div class="exam-card__inner text-center text-danger fw-bold fs-5 fade-in">
                <i class="fas fa-ban fa-2x mb-3"></i><br>
                {{ __('join.access_denied') }}
            </div>
        </div>
    </div>
    @php return; @endphp
@endif

<div class="overlay"></div>

<div class="container screen-shell d-flex justify-content-center align-items-start">
    <div class="exam-card quiz-runtime-fallback-target fade-in"
         data-quiz-question-runtime
         data-correct-count="{{ $correctCount }}"
         data-instruction-text="{{ $instructionText }}"
         data-selected-prefix="{{ __('join.selected') }}"
         data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}"
         data-attempt-id="{{ session('attempt_id') }}"
         data-force-submit-url="{{ route('quiz.force_submit') }}"
         data-csrf-token="{{ csrf_token() }}"
         data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}"
         data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}"
         data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}"
         data-server-now="{{ now()->timestamp }}"
         data-time-limit="{{ $quiz->time_limit }}"
         data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}"
         data-final-fallback-target=".quiz-runtime-fallback-target">
        <div class="exam-card__inner">
            <p class="quiz-context-title">{{ $quiz->title }}</p>

            <div class="hero-bar">
                <p class="question-progress-text">{{ $questionProgressLabel }}</p>
            </div>

        @if($showLearningFeedback)
            <div class="question-stage{{ $question->image ? ' question-stage--with-media' : '' }}">
                <div class="question-panel question-panel--headline">
                    <h1 class="question-title">{{ $question->text }}</h1>
                </div>

                <div class="question-content-column">
                    @if($question->image)
                        <div class="question-image-shell question-image-shell--inline">
                            <img src="{{ asset('storage/' . $question->image) }}"
                                 alt="{{ __('join.question_image_alt') }}"
                                 class="question-image">
                        </div>
                    @endif

                    <div class="mt-1">
                        @include('quiz.partials.learning-feedback-state')
                    </div>
                </div>

                @if($question->image)
                    <aside class="question-media-column">
                        <div class="question-image-shell">
                            <img src="{{ asset('storage/' . $question->image) }}"
                                 alt="{{ __('join.question_image_alt') }}"
                                 class="question-image">
                        </div>
                    </aside>
                @endif
            </div>
        @else
            @php
                $showSkipButton = !$isLastQuestion && !$isReviewPass;
            @endphp

            <div class="question-stage{{ $question->image ? ' question-stage--with-media' : '' }}">
                <div class="question-panel question-panel--headline">
                    <h1 class="question-title">{{ $question->text }}</h1>
                </div>

                <div class="question-content-column">
                    @if($question->image)
                        <div class="question-image-shell question-image-shell--inline">
                            <img src="{{ asset('storage/' . $question->image) }}"
                                 alt="{{ __('join.question_image_alt') }}"
                                 class="question-image">
                        </div>
                    @endif

                    <form id="quiz-form"
                          class="question-answer-form"
                          data-quiz-answer-form
                          action="{{ route('quiz.submit_answer', ['quizKey' => $quizRouteKey, 'questionKey' => $questionRouteKey]) }}"
                          method="POST">
                        @csrf
                        <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">

                        <div class="answer-list">
                            @foreach($question->answers as $answerIndex => $answer)
                                <label class="answer-option">
                                    @if($correctCount === 1)
                                        <input type="radio" name="answer_id[]" value="{{ $answer->id }}" class="answer-input">
                                    @else
                                        <input type="checkbox" name="answer_id[]" value="{{ $answer->id }}" class="answer-input">
                                    @endif
                                    <span class="answer-copy">
                                        @include('quiz.partials.answer-text', ['answerIndex' => $answerIndex, 'answer' => $answer, 'quiz' => $quiz])
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </form>

                </div>

                @if($question->image)
                    <aside class="question-media-column">
                        <div class="question-image-shell">
                            <img src="{{ asset('storage/' . $question->image) }}"
                                 alt="{{ __('join.question_image_alt') }}"
                                 class="question-image">
                        </div>
                    </aside>
                @endif

                <div class="selection-state question-stage__selection">
                    <p id="selection-info" data-selection-info></p>
                    <p id="selected-answer" data-selected-answer></p>
                </div>

                <div class="question-actions-row question-stage__actions{{ $showSkipButton ? '' : ' question-actions-row--single' }}">
                    <div class="actions-grid">
                        @if($isLastQuestion && !$isLearningMode)
                            <button type="submit"
                                    form="quiz-form"
                                    formaction="{{ route('quiz.submit_final', ['quizKey' => $quizRouteKey]) }}"
                                    class="btn btn-submit btn-success"
                                    id="submit-button"
                                    data-quiz-submit-button
                                    disabled>
                                <i class="fas fa-flag-checkered me-1"></i> {{ __('join.submit_quiz') }}
                            </button>
                        @else
                            <button type="submit"
                                    form="quiz-form"
                                    class="btn btn-submit"
                                    id="submit-button"
                                    data-quiz-submit-button
                                    disabled>
                                <i class="fas fa-paper-plane me-1"></i> {{ $isLearningMode ? __('join.learning_mode_check_answer') : __('join.submit_answer') }}
                            </button>
                        @endif
                    </div>

                    @if($showSkipButton)
                        <form action="{{ route('quiz.skip_question', ['quizKey' => $quizRouteKey, 'questionKey' => $questionRouteKey]) }}"
                              method="POST"
                              data-quiz-skip-form>
                            @csrf
                            <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">
                            <button type="submit" class="btn btn-skip">
                                <i class="fas fa-forward me-1"></i> {{ __('join.skip_question') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
            @endif

            @if($quiz->has_timer)
                <div class="timer-block">
                    <div class="progress">
                        <div class="progress-bar"
                             id="progress-bar"
                             data-time-progress
                             role="progressbar"
                             style="width: 100%;"
                             aria-valuemin="0"
                             aria-valuemax="100"
                             aria-valuenow="100"></div>
                    </div>
                    <p id="time-remaining" data-time-remaining>
                        <i class="fas fa-hourglass-half me-1"></i> {{ __('join.time_remaining') }} {{ $formattedTimeRemaining }}
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<template data-final-submit-fallback-template>
    <div id="final-submit-container" class="alert alert-info mt-4">
        <i class="fas fa-info-circle me-1"></i> {{ __('join.completed_message') }}
        <form method="POST" action="{{ route('quiz.submit_final', ['quizKey' => $quizRouteKey]) }}">
            @csrf
            <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">
            <button type="submit" class="btn btn-success w-100 mt-3">
                <i class="fas fa-flag-checkered me-1"></i> {{ __('join.submit_quiz') }}
            </button>
        </form>
    </div>
</template>
@endsection
