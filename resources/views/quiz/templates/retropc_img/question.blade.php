@extends('layouts.quiz_guest')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap');

    body {
        background-color: #0a0a0a;
        background-image:
            linear-gradient(0deg, rgba(0, 255, 255, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 255, 255, 0.05) 1px, transparent 1px);
        background-size: 40px 40px;
        background-attachment: fixed;
        font-family: 'Orbitron', sans-serif;
        color: #fff;
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: radial-gradient(circle at center, rgba(255, 0, 255, 0.06), rgba(0, 0, 0, 0.9));
        z-index: 1;
    }

    .card.neon-card {
        position: relative;
        padding: 0;
        background: transparent;
        border-radius: 1.25rem;
        overflow: hidden;
    }

    .neon-wrapper {
        position: relative;
        background: rgba(0, 0, 0, 0.7);
        padding: 2rem;
        border-radius: 1.25rem;
        box-shadow:
            0 0 10px rgba(3, 169, 244, 0.3),
            0 0 15px rgba(255, 0, 88, 0.2) inset;
        z-index: 2;
    }

    .neon-wrapper::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(315deg, #03a9f4, #ff0058);
        opacity: 0.2;
        z-index: -2;
        border-radius: 1.25rem;
    }

    .neon-wrapper::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(315deg, #03a9f4, #ff0058);
        filter: blur(20px);
        opacity: 0.15;
        z-index: -3;
        border-radius: 1.25rem;
    }

    .card-content {
        position: relative;
        z-index: 3;
        width: 100%;
    }

    h3 {
        color: #66fcf1;
        font-size: 1.4rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        text-align: center;
    }

    h5.text-muted {
        color: #a0fdfd !important;
        font-weight: bold;
        text-align: center;
        font-size: 1rem;
    }

    .question-image {
        max-height: 350px;
        object-fit: contain;
        border: 2px solid #00fff7;
        box-shadow: 0 0 10px #00fff7;
    }

    .list-group-item {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid #00fff7;
        color: #0ff;
        font-weight: bold;
        transition: 0.3s;
    }

    .list-group-item:hover {
        background-color: rgba(0, 255, 255, 0.15);
        box-shadow: 0 0 10px #00fff7;
    }

    .form-check-input {
        background-color: black;
        border-color: #00fff7;
    }

    .form-check-input:checked {
        background-color: #ff00ff;
        border-color: #ff00ff;
        box-shadow: 0 0 5px #ff00ff;
    }

    .btn-success, .btn-primary, .btn-warning {
        font-weight: bold;
        text-transform: uppercase;
        border: none;
        border-radius: 2rem;
        letter-spacing: 1px;
        box-shadow: 0 0 10px #00fff7;
        transition: 0.3s ease-in-out;
    }

    .btn-success {
        background: linear-gradient(135deg, #00fff7, #ff00ff);
        color: #000;
    }

    .btn-primary {
        background: linear-gradient(135deg, #ff00ff, #00fff7);
        color: #000;
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffff00, #ff6600);
        color: #000;
    }

    .btn:hover {
        box-shadow: 0 0 16px #00fff7, 0 0 20px #ff00ff;
        filter: brightness(1.05);
    }

    .progress {
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid #0ff;
        box-shadow: 0 0 6px #0ff;
    }

    .progress-bar {
        background: linear-gradient(90deg, #0f0, #ff0, #f00);
        animation: loading 2s linear infinite;
    }

    @keyframes loading {
        0% { background-position: 0% 0; }
        100% { background-position: 100% 0; }
    }

    #time-remaining {
        background-color: rgba(0, 255, 255, 0.07);
        padding: 0.5rem 1rem;
        border: 1px solid #00fff7;
        border-radius: 0.5rem;
        font-size: 1.3rem;
        font-weight: 600;
        color: #00fff7;
        margin-top: 1rem;
        display: inline-block;
        text-align: center;
    }

    .alert-info, .alert-danger {
        font-size: 0.9rem;
    }

    @media (max-width: 576px) {
        .neon-wrapper { padding: 1.5rem; }
        h3 { font-size: 1.1rem; }
        h5 { font-size: 0.9rem; }
    }
</style>

@php
    $correctCount = $question->answers->where('is_correct', true)->count();
    $instructionText = trans_choice('join.select_instruction', $correctCount, ['count' => $correctCount]);
    $showLearningFeedback = $showLearningFeedback ?? false;
    $isLearningMode = $isLearningMode ?? false;
@endphp

@if(!$allowDisplay)
    <div class="overlay"></div>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="card neon-card shadow text-center text-danger fw-bold fs-5 fade-in" style="max-width: 600px;" data-quiz-question-runtime data-correct-count="{{ $correctCount }}" data-instruction-text="{{ $instructionText }}" data-selected-prefix="{{ __('join.selected') }}" data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}" data-attempt-id="{{ session('attempt_id') }}" data-force-submit-url="{{ route('quiz.force_submit') }}" data-csrf-token="{{ csrf_token() }}" data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}" data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}" data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}" data-server-now="{{ now()->timestamp }}" data-time-limit="{{ $quiz->time_limit }}" data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}" data-final-fallback-target=".quiz-runtime-fallback-target">
            <div class="neon-wrapper quiz-runtime-fallback-target">
                <i class="fas fa-ban fa-2x my-3"></i> {{ __('join.access_denied') }}
            </div>
        </div>
    </div>
    @php return; @endphp
@endif

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card neon-card w-100 fade-in quiz-runtime-fallback-target" style="max-width: 800px;" data-quiz-question-runtime data-correct-count="{{ $correctCount }}" data-instruction-text="{{ $instructionText }}" data-selected-prefix="{{ __('join.selected') }}" data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}" data-attempt-id="{{ session('attempt_id') }}" data-force-submit-url="{{ route('quiz.force_submit') }}" data-csrf-token="{{ csrf_token() }}" data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}" data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}" data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}" data-server-now="{{ now()->timestamp }}" data-time-limit="{{ $quiz->time_limit }}" data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}" data-final-fallback-target=".quiz-runtime-fallback-target">
        <div class="neon-wrapper quiz-runtime-fallback-target">
            <div class="card-content p-3 w-100">
                <h5 class="text-muted mb-2">
                    {{ $questionProgressLabel }}
                </h5>

                <h3>{{ $question->text }}</h3>

                @if($question->image)
                    <div class="text-center my-3">
                        <img src="{{ asset('storage/' . $question->image) }}" alt="{{ __('join.question_image_alt') }}"
                             class="img-fluid rounded-3 shadow question-image">
                    </div>
                @endif

                @if($showLearningFeedback)
                    @include('quiz.partials.learning-feedback-state')
                @else
                    <form id="quiz-form" data-quiz-answer-form
                          action="{{ route('quiz.submit_answer', ['quizKey' => $quizRouteKey, 'questionKey' => $questionRouteKey]) }}"
                          method="POST"
                          >
                        @csrf
                <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">

                        <div class="list-group mt-4">
                            @foreach($question->answers as $answerIndex => $answer)
                                <label class="list-group-item list-group-item-action d-flex align-items-center">
                                    @if($correctCount === 1)
                                        <input type="radio" name="answer_id[]" value="{{ $answer->id }}" class="form-check-input me-2">
                                    @else
                                        <input type="checkbox" name="answer_id[]" value="{{ $answer->id }}" class="form-check-input me-2">
                                    @endif
                                    @include('quiz.partials.answer-text', ['answerIndex' => $answerIndex, 'answer' => $answer, 'quiz' => $quiz])
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <p id="selection-info" data-selection-info class="fw-semibold text-info small"></p>
                            <p id="selected-answer" data-selected-answer class="fw-semibold text-primary small"></p>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            @if($isLastQuestion && !$isLearningMode)
                                <button type="submit"
                                        formaction="{{ route('quiz.submit_final', ['quizKey' => $quizRouteKey]) }}"
                                        class="btn btn-success"
                                        id="submit-button" data-quiz-submit-button
                                        disabled>
                                    <i class="fas fa-flag-checkered me-1"></i> {{ __('join.submit_quiz') }}
                                </button>
                            @else
                                <button type="submit"
                                        class="btn btn-primary"
                                        id="submit-button" data-quiz-submit-button
                                        disabled>
                                    <i class="fas fa-paper-plane me-1"></i> {{ $isLearningMode ? __('join.learning_mode_check_answer') : __('join.submit_answer') }}
                                </button>
                            @endif
                        </div>
                    </form>

                    @if(!$isLastQuestion && !$isReviewPass)
                        <form action="{{ route('quiz.skip_question', ['quizKey' => $quizRouteKey, 'questionKey' => $questionRouteKey]) }}"
                              method="POST" class="mt-3" data-quiz-skip-form
                              >
                            @csrf
                <input type="hidden" name="current_question_key" value="{{ $questionRouteKey }}">
                            <button type="submit" class="btn btn-warning w-100">
                                <i class="fas fa-forward me-1"></i> {{ __('join.skip_question') }}
                            </button>
                        </form>
                    @endif
                @endif

                @if($quiz->has_timer)
                    <div class="progress mt-4" style="height: 20px;">
                        <div class="progress-bar bg-success" id="progress-bar" data-time-progress role="progressbar" style="width: 100%;" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p id="time-remaining" data-time-remaining>
                        <i class="fas fa-hourglass-half me-1"></i> {{ __('join.time_remaining') }} {{ gmdate("i:s", $timeRemaining) }}
                    </p>
                @endif
            </div>
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





