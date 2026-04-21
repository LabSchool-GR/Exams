@extends('layouts.quiz_guest')

@section('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=Noto+Sans+Display:wght@700;800;900&family=VT323&display=swap" rel="stylesheet">
@endsection

@section('content')
@include('quiz.templates.retroAXD3_img.partials.theme')

<style>
.retro-quiz-shell {
    display: grid;
    gap: 1rem;
}

.retro-question-screen {
    min-height: var(--retro-stage-min-height);
    display: grid;
    gap: 1.15rem;
    align-content: start;
}

.retro-question-content {
    display: grid;
    grid-template-columns: minmax(180px, 240px) minmax(0, 1fr);
    gap: 1.1rem;
    align-items: center;
}

.retro-question-copy {
    display: grid;
    gap: 0.7rem;
    align-content: center;
    text-align: left;
    padding: clamp(0.35rem, 1vw, 0.65rem) 0 0;
}

.retro-question-kicker {
    margin: 0;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.82rem, 1.1vw, 1rem);
    font-weight: 700;
    color: #efe7ff;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    text-shadow:
        2px 0 0 rgba(255, 79, 207, 0.42),
        -2px 0 0 rgba(89, 242, 255, 0.3);
}

.retro-question-text {
    margin: 0;
    max-width: none;
    color: #fff9ef;
    font-size: clamp(1.45rem, 2vw, 2rem);
    line-height: 1.18;
    font-family: "VT323", "IBM Plex Mono", monospace;
    letter-spacing: 0.01em;
    text-shadow:
        2px 0 0 rgba(255, 79, 207, 0.34),
        -2px 0 0 rgba(89, 242, 255, 0.24);
}

.retro-question-media {
    width: 100%;
    max-width: 240px;
    justify-self: start;
}

.retro-question-media img {
    display: block;
    width: 100%;
    max-height: 220px;
    object-fit: contain;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.03);
}

.retro-answer-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 1rem 1.15rem;
    margin-top: auto;
}

.retro-answer-button {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
    min-height: 62px;
    padding: 0.9rem 1rem;
    border-radius: 8px;
    border: 4px solid #05070d;
    cursor: pointer;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.16),
        0 6px 0 rgba(0, 0, 0, 0.82);
    transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
}

.retro-answer-button::after {
    content: "";
    position: absolute;
    inset: 7px;
    border-radius: 4px;
    border: 2px solid rgba(255, 255, 255, 0.88);
    box-shadow:
        inset 0 0 0 1px rgba(255, 255, 255, 0.18),
        0 0 0 1px rgba(5, 7, 13, 0.5);
    opacity: 0;
    transition: opacity 0.12s ease;
    pointer-events: none;
}

.retro-answer-button:hover,
.retro-answer-button:focus-within {
    transform: translateY(1px);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.16),
        0 4px 0 rgba(0, 0, 0, 0.82);
    filter: brightness(1.05);
}

.retro-answer-button:has(input:checked) {
    transform: translateY(1px);
    border-color: rgba(255, 255, 255, 0.96);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.16),
        0 4px 0 rgba(0, 0, 0, 0.82),
        0 0 0 2px rgba(255, 255, 255, 0.12),
        0 0 28px var(--retro-answer-glow);
    filter: brightness(1.08) saturate(1.12);
}

.retro-answer-button:has(input:checked)::after {
    opacity: 1;
}

.retro-answer-button--red {
    --retro-answer-glow: rgba(255, 33, 95, 0.55);
    background: linear-gradient(180deg, #ff215f, #cb003c);
    color: #fff3fa;
}

.retro-answer-button--cyan {
    --retro-answer-glow: rgba(91, 243, 255, 0.62);
    background: linear-gradient(180deg, #5bf3ff, #26dbe7);
    color: #071219;
}

.retro-answer-button--green {
    --retro-answer-glow: rgba(136, 255, 73, 0.58);
    background: linear-gradient(180deg, #88ff49, #48dd16);
    color: #081106;
}

.retro-answer-button--yellow {
    --retro-answer-glow: rgba(255, 233, 90, 0.6);
    background: linear-gradient(180deg, #ffe95a, #f3d300);
    color: #171307;
}

.retro-answer-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.retro-answer-copy {
    text-align: center;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.95rem, 1.3vw, 1.08rem);
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.retro-answer-prefix {
    margin-right: 0.45rem;
}

.retro-selection-row {
    display: grid;
    gap: 0.3rem;
    margin-top: 0.2rem;
}

.retro-selection-row p {
    margin: 0;
    font-family: "IBM Plex Mono", monospace;
    font-size: 0.9rem;
    color: var(--retro-muted);
    text-align: center;
    letter-spacing: 0.06em;
}

.retro-selection-row #selected-answer {
    color: var(--retro-text);
}

.retro-action-wrap {
    display: flex;
    justify-content: center;
}

.retro-action-wrap .retro-action {
    width: min(100%, 320px);
}

.retro-learning-panel {
    min-height: var(--retro-stage-min-height);
    display: grid;
    align-content: center;
    gap: 1rem;
}

.retro-no-access {
    min-height: var(--retro-stage-min-height);
    display: grid;
    align-content: center;
    gap: 1rem;
    text-align: center;
}

@media (max-width: 700px) {
    .retro-question-screen {
        min-height: var(--retro-stage-min-height-mobile);
    }

    .retro-question-content {
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }

    .retro-question-text {
        font-size: clamp(1.35rem, 6vw, 1.8rem);
        text-align: center;
    }

    .retro-question-copy {
        text-align: center;
    }

    .retro-question-media {
        justify-self: center;
    }

    .retro-answer-grid {
        grid-template-columns: 1fr;
        gap: 0.8rem;
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
    $answerColorClasses = [
        'retro-answer-button--red',
        'retro-answer-button--cyan',
        'retro-answer-button--green',
        'retro-answer-button--yellow',
    ];
@endphp

@if(!$allowDisplay)
    <div class="retro-page">
        <div class="retro-frame">
            <div class="retro-monitor fade-in"
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
                <div class="retro-screen">
                    <div class="retro-screen__inner retro-no-access">
                        <div class="retro-screen-bar">
                            <span class="retro-screen-bar__label">Play</span>
                            <span class="retro-screen-meta">guest mode</span>
                        </div>
                        <div class="retro-screen-heading">
                            <h1 class="retro-screen-heading__title">{{ $quiz->title }}</h1>
                            <p class="retro-screen-heading__subtitle">quiz unavailable</p>
                        </div>
                        <p class="retro-screen-copy">{{ __('join.access_denied') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php return; @endphp
@endif

<div class="retro-page">
    <div class="retro-frame">
        <div class="retro-monitor fade-in quiz-runtime-fallback-target"
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
            <div class="retro-screen">
                <div class="retro-screen__inner retro-quiz-shell">
                    <div class="retro-screen-bar">
                        <span class="retro-screen-bar__label">Play</span>
                        @if($quiz->has_timer)
                            <span class="retro-screen-meta">
                                <span class="retro-led" data-time-remaining>{{ __('join.time_remaining') }} {{ $formattedTimeRemaining }}</span>
                            </span>
                        @else
                            <span class="retro-screen-meta">guest mode</span>
                        @endif
                    </div>

                    <div class="retro-screen-heading">
                        <h1 class="retro-screen-heading__title">{{ $quiz->title }}</h1>
                        <p class="retro-screen-heading__subtitle">{{ $questionProgressLabel }}</p>
                    </div>

                    @if($showLearningFeedback)
                        <div class="retro-learning-panel">
                            <div class="retro-question-content">
                                @if($question->image)
                                    <div class="retro-question-media">
                                        <img src="{{ asset('storage/' . $question->image) }}" alt="{{ __('join.question_image_alt') }}">
                                    </div>
                                @endif
                                <div class="retro-question-copy">
                                    <p class="retro-question-kicker">&gt; {{ $questionProgressLabel }}:</p>
                                    <h1 class="retro-question-text">{{ $question->text }}</h1>
                                </div>
                            </div>

                            <div class="retro-panel">
                                @include('quiz.partials.learning-feedback-state')
                            </div>

                            <div class="retro-tape-bar"></div>
                        </div>
                    @else
                        <div class="retro-question-screen">
                            <div class="retro-question-content">
                                @if($question->image)
                                    <div class="retro-question-media">
                                        <img src="{{ asset('storage/' . $question->image) }}" alt="{{ __('join.question_image_alt') }}">
                                    </div>
                                @endif
                                <div class="retro-question-copy">
                                    <p class="retro-question-kicker">&gt; {{ $questionProgressLabel }}:</p>
                                    <h1 class="retro-question-text">{{ $question->text }}</h1>
                                </div>
                            </div>

                            <form id="quiz-form"
                                  data-quiz-answer-form
                                  action="{{ route('quiz.submit_answer', ['quizKey' => $quizRouteKey, 'questionKey' => $questionRouteKey]) }}"
                                  method="POST">
                                @csrf
                                <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">

                                <div class="retro-answer-grid">
                                    @foreach($question->answers as $answerIndex => $answer)
                                        @php
                                            $colorClass = $answerColorClasses[$answerIndex % count($answerColorClasses)];
                                            $slotLabel = $quiz->answerLabelForIndex($answerIndex, ')');
                                        @endphp
                                        <label class="retro-answer-button {{ $colorClass }}">
                                            @if($correctCount === 1)
                                                <input type="radio" name="answer_id[]" value="{{ $answer->id }}" class="retro-answer-input">
                                            @else
                                                <input type="checkbox" name="answer_id[]" value="{{ $answer->id }}" class="retro-answer-input">
                                            @endif
                                            <span class="retro-answer-copy">
                                                @if($slotLabel)
                                                    <span class="retro-answer-prefix">{{ $slotLabel }}</span>
                                                @endif
                                                @include('quiz.partials.answer-text', ['answerIndex' => $answerIndex, 'answer' => $answer, 'quiz' => $quiz, 'showPrefix' => false])
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </form>

                            <div class="retro-selection-row">
                                <p id="selection-info" data-selection-info>{{ $instructionText }}</p>
                                <p id="selected-answer" data-selected-answer></p>
                            </div>

                            <div class="retro-action-wrap">
                                @if($isLastQuestion && !$isLearningMode)
                                    <button type="submit"
                                            form="quiz-form"
                                            formaction="{{ route('quiz.submit_final', ['quizKey' => $quizRouteKey]) }}"
                                            class="retro-action retro-action--primary"
                                            id="submit-button"
                                            data-quiz-submit-button
                                            disabled>
                                        {{ __('join.submit_quiz') }}
                                    </button>
                                @else
                                    <button type="submit"
                                            form="quiz-form"
                                            class="retro-action retro-action--primary"
                                            id="submit-button"
                                            data-quiz-submit-button
                                            disabled>
                                        {{ $isLearningMode ? __('join.learning_mode_check_answer') : __('join.submit_answer') }}
                                    </button>
                                @endif
                            </div>

                            @if($quiz->has_timer)
                                <div class="d-none">
                                    <div data-time-progress class="progress-bar" style="width: 100%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="100"></div>
                                </div>
                            @endif

                            <div class="retro-tape-bar"></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<template data-final-submit-fallback-template>
    <div id="final-submit-container" class="retro-panel mt-3">
        <p class="retro-note mb-3">{{ __('join.completed_message') }}</p>
        <form method="POST" action="{{ route('quiz.submit_final', ['quizKey' => $quizRouteKey]) }}">
            @csrf
            <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">
            <button type="submit" class="retro-action retro-action--primary">
                {{ __('join.submit_quiz') }}
            </button>
        </form>
    </div>
</template>
@endsection
