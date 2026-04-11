@extends('layouts.quiz_guest')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap');

    body {
        background-color: #000;
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
        background: radial-gradient(circle at center, rgba(0, 255, 255, 0.05), rgba(0, 0, 0, 0.9));
        z-index: 1;
    }

    .card-glass {
        background: rgba(0, 0, 0, 0.65);
        border: 2px solid #00fff7;
        box-shadow: 0 0 18px #00fff7 inset;
        backdrop-filter: blur(8px);
        border-radius: 1.5rem;
        padding: 2.5rem;
        z-index: 2;
    }

    h3, h5 {
        text-transform: uppercase;
        font-weight: bold;
        letter-spacing: 1px;
    }

    h3 {
        color: #00ff99;
        text-shadow: 0 0 3px #00ff99;
    }

    h5 {
        color: #00fff7;
        text-shadow: 0 0 2px #00fff7;
    }

    .text-muted {
        color: #a0fdfd !important;
        font-size: 0.9rem;
    }

    .result-summary {
        background-color: rgba(0, 0, 0, 0.6);
        border: 2px solid #00fff7;
        box-shadow: 0 0 10px #00fff7;
        font-size: 0.95rem;
        color: #0ff;
    }

    .result-summary h5 {
        color: #00fff7;
        font-weight: bold;
        text-shadow: 0 0 4px #00fff7;
    }

    .alert-success, .alert-danger {
        border-radius: 0.75rem;
        border: 1px solid;
        font-size: 0.85rem;
        padding: 0.75rem 1rem;
        font-weight: 600;
    }

    .alert-success {
        background-color: rgba(0, 255, 100, 0.08);
        border-color: #00ff66;
        color: #00ff66;
        box-shadow: 0 0 10px #00ff66;
    }

    .alert-danger {
        background-color: rgba(255, 0, 50, 0.08);
        border-color: #ff0033;
        color: #ff0033;
        box-shadow: 0 0 10px #ff0033;
    }

    .btn {
        font-weight: bold;
        letter-spacing: 1px;
        border-radius: 2rem;
        box-shadow: 0 0 8px #00fff7;
        transition: 0.3s;
        text-transform: uppercase;
    }

    .btn-outline-primary {
        border-color: #00fff7;
        color: #00fff7;
    }

    .btn-outline-primary:hover {
        background-color: #00fff7;
        color: #000;
        box-shadow: 0 0 15px #00fff7;
    }

    .btn-outline-dark {
        border-color: #00ccc9;
        color: #00ccc9;
    }

    .btn-outline-dark:hover {
        background-color: #00ccc9;
        color: #000;
        box-shadow: 0 0 15px #00ccc9;
    }

    .btn-outline-secondary {
        border-color: #666;
        color: #ccc;
    }

    .btn-outline-secondary:disabled {
        background-color: rgba(255, 255, 255, 0.05);
        color: #888;
        border-color: #444;
    }

    .external-links .btn {
        white-space: nowrap;
        font-size: 0.9rem;
    }

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }

        h3 {
            font-size: 1.2rem;
        }

        h5 {
            font-size: 1rem;
        }

        .external-links .btn {
            font-size: 0.8rem;
        }
    }
</style>

@php
    $isLearningModeResult = $isLearningModeResult ?? false;
@endphp

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg text-center w-100" style="max-width: 700px;">
        <h3 class="mb-2">
            <i class="fas fa-check-circle me-2"></i> {{ __('join.quiz_completed') }}
        </h3>

        <h5 class="mb-3">
            <i class="fas fa-user-graduate me-2"></i> {{ __('join.congrats', ['name' => $attempt->student_name]) }}
        </h5>

        <p class="text-muted mb-4">
            {{ $isLearningModeResult ? __('join.learning_mode_result_message') : __('join.quiz_success') }}
        </p>

        @if($isLearningModeResult)
            <div class="alert alert-info mt-2">
                <i class="fas fa-graduation-cap me-1"></i>
                {{ __('join.learning_mode_result_message') }}
            </div>
        @else
            <div class="row justify-content-center mb-3">
                <div class="col-md-8">
                    <div class="result-summary p-3 rounded shadow-sm">
                        <h5 class="mb-3">
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
                <div class="alert alert-success mt-2">
                    <i class="fas fa-thumbs-up me-1"></i>
                    {{ __('join.passed', ['score' => number_format($scorePercentage, 2), 'required' => $quiz->pass_percentage]) }}
                </div>
            @else
                <div class="alert alert-danger mt-2">
                    <i class="fas fa-times-circle me-1"></i>
                    {{ __('join.failed', ['score' => number_format($scorePercentage, 2), 'required' => $quiz->pass_percentage]) }}
                </div>
            @endif
        @endif

{{-- Keep result actions grouped so retry and download options stay visually related. --}}
		<div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-4 mt-5">
            @unless($isLearningModeResult)
				@if($attempt->student_code === '0000')
					<button class="btn btn-outline-secondary btn-sm fs-6" disabled>
						{{ __('join.pdf_unavailable') }}
					</button>
				@else
					<a href="{{ URL::temporarySignedRoute('quiz_attempts.download_pdf_signed', now()->addMinutes((int) config('security.signed_urls.attempt_pdf_ttl_minutes', 1440)), [$quiz, $attempt]) }}" class="btn btn-outline-primary btn-sm fs-6">
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
					<a href="{{ $retryUrl }}" class="btn btn-warning btn-sm fs-6">
						{{ __('join.retry_quiz') }}
					</a>
				@endif
            @endunless

			<a href="{{ route('quiz.join') }}" class="btn btn-outline-dark btn-sm fs-6">
					{{ __('join.back_to_home') }}
			</a>
		</div>
		
		@if(!$isLearningModeResult && $attempt->student_code === '0000')
			<div class="text-center mt-3">
				<p class="text-muted small">{{ __('join.pdf_note') }}</p>
			</div>
		@endif

{{-- Show the external organization links separately from the result actions. --}}
<div class="row justify-content-center mt-4 external-links g-3">
    <div class="col-12 col-md-4">
        <a href="https://steth.gr" target="_blank" rel="noopener" class="btn btn-outline-primary w-100">
            <i class="fas fa-globe me-1"></i> STETH.GR
        </a>
    </div>
    <div class="col-12 col-md-4">
        <a href="https://www.facebook.com/syllogostexnologiasthrakis" target="_blank" rel="noopener" class="btn btn-outline-primary w-100">
            <i class="fab fa-facebook me-1"></i> FB STETH
        </a>
    </div>
    <div class="col-12 col-md-4">
        <a href="https://www.facebook.com/profile.php?id=61556225845165" target="_blank" rel="noopener" class="btn btn-outline-primary w-100">
            <i class="fab fa-facebook-f me-1"></i> FB Retro G AXD
        </a>
    </div>
</div>

    </div>
</div>
@endsection
