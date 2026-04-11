@extends('layouts.quiz_guest')

@section('content')
<style>
:root {
    --exam-bg: #f7f2ea;
    --exam-paper: #ffffff;
    --exam-primary: #26436b;
    --exam-accent: #3b82f6;
    --exam-muted: #7b7f8e;
    --exam-border-soft: rgba(31, 41, 55, 0.08);
}

body {
    position: relative;
    min-height: 100vh;
    background: var(--exam-bg);
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
}

body::before {
    content: "";
    position: fixed;
    inset: 0;
    background-image: url('{{ asset('storage/university.jpg') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;

    opacity: 0.55;
    filter: blur(3px);
    transform: scale(1.03);
    z-index: -2;
}

.container {
    position: relative;
    z-index: 2;
    min-height: 100vh;
}

.card-glass {
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.9), rgba(249, 250, 252, 0.98));
    border-radius: 1.4rem;
    padding: 1.75rem 2rem;
    border: 1px solid var(--exam-border-soft);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
}

.fade-in {
    animation: fadeInUp 0.45s ease-out;
}

.exam-header-small {
    text-align: center;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--exam-muted);
    margin-bottom: 0.2rem;
}

.question-number {
    text-align: center;
    font-size: 0.9rem;
    color: var(--exam-muted);
    margin-bottom: 0.6rem;
}

.question-title {
    text-align: center;
    font-weight: 600;
    font-size: 1.05rem;
    line-height: 1.6;
    color: var(--exam-primary);
    margin-bottom: 0.9rem;
}

.question-image {
    max-height: 280px;
    object-fit: contain;
    border-radius: 1rem;
    border: 1px solid rgba(148, 163, 184, 0.45);
    background: #f9fafb;
}

.answers-wrapper {
    margin-top: 1.2rem;
}

.list-group {
    border-radius: 1.1rem;
    overflow: hidden;
    border: 1px solid var(--exam-border-soft);
    background: #f9fafb;
}

.list-group-item {
    border: 0;
    padding: 0.75rem 1rem;
    font-size: 0.94rem;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    background: transparent;
    transition:
        background 0.15s ease,
        border-color 0.15s ease,
        transform 0.08s ease,
        box-shadow 0.15s ease;
    border-left: 3px solid transparent;
}

.list-group-item + .list-group-item {
    border-top: 1px solid rgba(148, 163, 184, 0.25);
}

.list-group-item:hover {
    background: #ffffff;
    transform: translateY(-1px);
    border-left-color: rgba(59, 130, 246, 0.55);
    box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
    cursor: pointer;
}

/* Keep the selected answer visually distinct without changing the base card layout. */
.list-group-item:has(input:checked) {
    background: #eef2ff;
    border-left-color: var(--exam-accent);
    box-shadow: 0 12px 26px rgba(37, 99, 235, 0.18);
}

.list-group-item input:checked + span {
    color: var(--exam-primary);
    font-weight: 600;
}

.form-check-input {
    cursor: pointer;
    width: 1.05rem;
    height: 1.05rem;
    flex-shrink: 0;
    accent-color: var(--exam-accent);
}

#selection-info,
#selected-answer {
    min-height: 1.1rem;
    font-size: 0.85rem;
}

#selection-info {
    color: var(--exam-muted);
}

#selected-answer {
    color: var(--exam-primary);
}

.progress {
    background-color: #e5e7eb;
    border-radius: 999px;
    overflow: hidden;
}

.progress-bar {
    background: linear-gradient(90deg, #3b82f6, #10b981);
}

#time-remaining {
    font-size: 0.9rem;
    color: var(--exam-primary);
}

#time-remaining.text-danger {
    color: #b91c1c !important;
}


.btn {
    border-radius: 999px;
    font-size: 0.94rem;
}


@keyframes fadeInUp {
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
    .card-glass {
        padding: 1.4rem 1.2rem;
        border-radius: 1.1rem;
    }

    .question-title {
        font-size: 0.98rem;
    }

    .list-group-item {
        padding: 0.7rem 0.85rem;
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
            <i class="fas fa-ban fa-2x my-3"></i> {{ __('join.access_denied') }}
        </div>
    </div>
    @php return; @endphp
@endif

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100 fade-in quiz-runtime-fallback-target" style="max-width: 800px;" data-quiz-question-runtime data-correct-count="{{ $correctCount }}" data-instruction-text="{{ $instructionText }}" data-selected-prefix="{{ __('join.selected') }}" data-allow-resume="{{ $quiz->allow_resume ? 'true' : 'false' }}" data-attempt-id="{{ session('attempt_id') }}" data-force-submit-url="{{ route('quiz.force_submit') }}" data-csrf-token="{{ csrf_token() }}" data-end-quiz-url="{{ route('quiz.end', ['quizKey' => $quizRouteKey]) }}" data-has-timer="{{ $quiz->has_timer ? 'true' : 'false' }}" data-end-time="{{ Session::has('quiz_end_time') ? \Carbon\Carbon::parse(Session::get('quiz_end_time'))->timestamp : 0 }}" data-server-now="{{ now()->timestamp }}" data-time-limit="{{ $quiz->time_limit }}" data-last-question="{{ $isLastQuestion ? 'true' : 'false' }}" data-final-fallback-target=".quiz-runtime-fallback-target">
        <div class="exam-header-small">
            {{ __('join.uni_module_header') }}
        </div>

		<div class="question-number">
			{{ $questionProgressLabel }}
		</div>

		<h3 class="question-title">{{ $question->text }}</h3>

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

				<div class="answers-wrapper">
					<div class="list-group">
						@foreach($question->answers as $answerIndex => $answer)
							<label class="list-group-item">
								@if($correctCount === 1)
									<input type="radio" name="answer_id[]" value="{{ $answer->id }}" class="form-check-input">
								@else
									<input type="checkbox" name="answer_id[]" value="{{ $answer->id }}" class="form-check-input">
								@endif
								<span>@include('quiz.partials.answer-text', ['answerIndex' => $answerIndex, 'answer' => $answer, 'quiz' => $quiz])</span>
							</label>
						@endforeach
					</div>
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





