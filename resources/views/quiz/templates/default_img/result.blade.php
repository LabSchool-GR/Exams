@extends('layouts.quiz_guest')

@section('content')
<style>
    body {
        background-image: url('{{ asset('storage/bg-quiz.jpg') }}');
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

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }
    }

    .result-summary {
        font-size: 1rem;
    }
</style>

@php
    $isLearningModeResult = $isLearningModeResult ?? false;
@endphp

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg text-center w-100" style="max-width: 700px;">
        <h3 class="text-success fw-bold mb-2">
            <i class="fas fa-check-circle me-2"></i> {{ __('join.quiz_completed') }}
        </h3>

        <h5 class="fw-bold text-dark mb-3">
            <i class="fas fa-user-graduate me-2"></i> {{ __('join.congrats', ['name' => $attempt->student_name]) }}
        </h5>

        <p class="text-muted mb-4">
            {{ $isLearningModeResult ? __('join.learning_mode_result_message') : __('join.quiz_success') }}
        </p>

        @if($isLearningModeResult)
            <div class="alert alert-info mt-3">
                <i class="fas fa-graduation-cap me-1"></i>
                {{ __('join.learning_mode_result_message') }}
            </div>
        @else
            <div class="row justify-content-center mb-3">
                <div class="col-md-8">
                    <div class="bg-white border rounded shadow-sm p-3 result-summary">
                        <h5 class="fw-bold text-primary mb-3">
                            <i class="fas fa-chart-bar me-1"></i> {{ __('join.results') }}
                        </h5>
                        <p class="mb-2">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            {{ __('join.correct_answers', ['correct' => $correctCount, 'total' => $totalQuestions]) }}
                        </p>
                        <p class="mb-2">
                            <i class="fas fa-percentage text-info me-1"></i>
                            {{ __('join.score_percentage', ['score' => number_format($scorePercentage, 2)]) }}
                        </p>
                    </div>
                </div>
            </div>

            @if($scorePercentage >= $quiz->pass_percentage)
                <div class="alert alert-success fw-semibold small mt-2">
                    <i class="fas fa-thumbs-up me-1"></i>
                    {{ __('join.passed', ['score' => number_format($scorePercentage, 2), 'required' => $quiz->pass_percentage]) }}
                </div>
            @else
                <div class="alert alert-danger fw-semibold small mt-2">
                    <i class="fas fa-times-circle me-1"></i>
                    {{ __('join.failed', ['score' => number_format($scorePercentage, 2), 'required' => $quiz->pass_percentage]) }}
                </div>
            @endif
        @endif

        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-3 mt-4">
            @unless($isLearningModeResult)
                @if($attempt->student_code === '0000')
                    <button class="btn btn-outline-secondary" disabled>
                        <i class="fas fa-file-pdf me-1"></i> {{ __('join.pdf_unavailable') }}
                    </button>
                    <p class="text-muted small mt-1">{{ __('join.pdf_note') }}</p>
                @else
                    <a href="{{ URL::temporarySignedRoute('quiz_attempts.download_pdf_signed', now()->addMinutes((int) config('security.signed_urls.attempt_pdf_ttl_minutes', 1440)), [$quiz, $attempt]) }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf me-1"></i> {{ __('join.download_pdf') }}
                    </a>
                @endif

				@php
					$canRetryAsGuest = $attempt->student_code === '0000'
						&& $scorePercentage < $quiz->pass_percentage
						&& $quiz->is_public
						&& $quiz->allow_guest;

					$canRetryAsStudent = $attempt->student_code !== '0000'
						&& $scorePercentage < $quiz->pass_percentage
						&& $remainingAttempts > 0
						&& $quiz->supportsStudentPersonalLinks();

					$retryUrl = null;

					if ($canRetryAsGuest) {
						$retryUrl = $quiz->publicAccessUrl();
					} elseif ($canRetryAsStudent && !empty($attempt->student_code)) {
						$student = $attempt->student;

						if (!$student) {
							$student = \App\Models\QuizStudent::where('quiz_id', $quiz->id)
								->where('student_code', $attempt->student_code)
								->first();
						}

						if ($student) {
							$retryUrl = $student->accessLinkUrl();
						}
					}
				@endphp

				@if ($retryUrl)
					<a href="{{ $retryUrl }}" class="btn btn-warning">
						<i class="fas fa-redo me-1"></i> {{ __('join.retry_quiz') }}
					</a>
				@endif
            @endunless

            <a href="{{ route('quiz.join') }}" class="btn btn-outline-dark">
                <i class="fas fa-arrow-left me-1"></i> {{ __('join.back_to_home') }}
            </a>
        </div>
    </div>
</div>
@endsection
