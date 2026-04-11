@extends('layouts.quiz_guest')

@section('content')
<style>
    body {
        background-image: url('{{ asset('storage/' . ($quiz->image ?? 'bg-quiz.jpg')) }}');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.7);
        z-index: 1;
    }

    .card-glass {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(6px);
        border-radius: 1rem;
        padding: 2rem;
        z-index: 2;
    }

    .fade-in {
        opacity: 0;
        animation: fadeIn 2s ease-in-out forwards;
        animation-fill-mode: both;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }
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
        <div class="card card-glass shadow text-center text-danger fw-bold fs-5 fade-in" style="max-width: 600px;" data-quiz-question-runtime data-correct-count="{{ $correctCount }}" data-instruction-text="{{ $instructionText }}" data-selected-prefix="{{ __('join.selected') }}" data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}" data-attempt-id="{{ session('attempt_id') }}" data-force-submit-url="{{ route('quiz.force_submit') }}" data-csrf-token="{{ csrf_token() }}" data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}" data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}" data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}" data-server-now="{{ now()->timestamp }}" data-time-limit="{{ $quiz->time_limit }}" data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}" data-final-fallback-target=".quiz-runtime-fallback-target">
            <i class="fas fa-ban fa-2x my-3"></i>
            {{ __('join.access_denied') }}
        </div>
    </div>
    @php return; @endphp
@endif

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100 fade-in quiz-runtime-fallback-target" style="max-width: 700px;" data-quiz-question-runtime data-correct-count="{{ $correctCount }}" data-instruction-text="{{ $instructionText }}" data-selected-prefix="{{ __('join.selected') }}" data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}" data-attempt-id="{{ session('attempt_id') }}" data-force-submit-url="{{ route('quiz.force_submit') }}" data-csrf-token="{{ csrf_token() }}" data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}" data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}" data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}" data-server-now="{{ now()->timestamp }}" data-time-limit="{{ $quiz->time_limit }}" data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}" data-final-fallback-target=".quiz-runtime-fallback-target">
        <h5 class="text-center text-muted mb-2">
            {{ $questionProgressLabel }}
        </h5>

        <h3 class="text-center text-primary fw-bold">{{ $question->text }}</h3>

        @if($question->image)
            <div class="text-center my-3">
                <img src="{{ asset('storage/' . $question->image) }}"
                     alt="{{ __('join.question_image_alt') }}"
                     class="img-fluid rounded shadow">
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
            <p class="text-center text-danger fw-bold mt-2" id="time-remaining" data-time-remaining>
                <i class="fas fa-hourglass-half me-1"></i> {{ __('join.time_remaining') }} {{ gmdate("i:s", $timeRemaining) }}
            </p>
        @endif
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





