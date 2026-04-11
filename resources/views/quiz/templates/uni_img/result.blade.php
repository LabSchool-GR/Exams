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
    --exam-success: #15803d;
    --exam-fail: #b91c1c;
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

.overlay {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at top,
        rgba(255, 255, 255, 0.35),
        rgba(249, 250, 252, 0.45)
    );
    z-index: 1;
    pointer-events: none;
}


.container {
    position: relative;
    z-index: 2;
    min-height: 100vh;
}

.card-glass {
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.98), rgba(249, 250, 252, 0.99));
    border-radius: 1.4rem;
    padding: 1.9rem 2.2rem;
    border: 1px solid var(--exam-border-soft);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
}

.exam-header-small {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--exam-muted);
    margin-bottom: 0.7rem;
    text-align: center;
}

.result-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--exam-primary);
    text-align: center;
    margin-bottom: 0.5rem;
}

.result-subtitle {
    font-size: 0.95rem;
    color: var(--exam-muted);
    text-align: center;
    margin-bottom: 0.9rem;
}

.student-line {
    font-size: 0.95rem;
    color: var(--exam-muted);
    text-align: center;
    margin-bottom: 1rem;
}

.student-line strong {
    color: var(--exam-primary);
}

.result-summary-box {
    background: #ffffff;
    border-radius: 1.1rem;
    border: 1px solid var(--exam-border-soft);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
    padding: 1.25rem 1.4rem;
    font-size: 0.95rem;
}

.result-summary-box h5 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--exam-primary);
    margin-bottom: 0.8rem;
}

.result-summary-box p {
    margin-bottom: 0.35rem;
}

.status-pill {
    margin-top: 1rem;
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.status-pill.pass {
    background: #dcfce7;
    color: var(--exam-success);
    border: 1px solid rgba(22, 163, 74, 0.4);
}

.status-pill.fail {
    background: #fee2e2;
    color: var(--exam-fail);
    border: 1px solid rgba(185, 28, 28, 0.4);
}

.result-note {
    font-size: 0.85rem;
    color: var(--exam-muted);
    margin-top: 0.7rem;
}

.btn {
    border-radius: 999px;
    font-size: 0.94rem;
}

.actions-row {
    margin-top: 1.6rem;
}

@media (max-width: 576px) {
    .card-glass {
        padding: 1.6rem 1.4rem;
        border-radius: 1.1rem;
    }

    .result-title {
        font-size: 1.15rem;
    }
}
</style>

@php
    $isLearningModeResult = $isLearningModeResult ?? false;
@endphp


<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center">
    <div class="card card-glass shadow-lg text-center w-100" style="max-width: 720px;">

        <div class="exam-header-small">
            {{ __('join.uni_module_header') }}
        </div>

        <h3 class="result-title">
            <i class="fas fa-check-circle me-2"></i> {{ __('join.quiz_completed') }}
        </h3>

        <h5 class="result-subtitle">
            {{ __('join.congrats', ['name' => $attempt->student_name]) }}
        </h5>

        <p class="student-line">
            {{ $isLearningModeResult ? __('join.learning_mode_result_message') : __('join.quiz_success') }}
        </p>

        @if($isLearningModeResult)
            <div class="alert alert-info mt-3">
                <i class="fas fa-graduation-cap me-1"></i>
                {{ __('join.learning_mode_result_message') }}
            </div>
        @else
            <div class="row justify-content-center mb-1">
                <div class="col-md-8">
                    <div class="result-summary-box">
                        <h5>
                            <i class="fas fa-chart-bar me-1"></i> {{ __('join.results') }}
                        </h5>
                        <p>
                            <i class="fas fa-check-circle text-success me-1"></i>
                            {{ __('join.correct_answers', ['correct' => $correctCount, 'total' => $totalQuestions]) }}
                        </p>
                        <p>
                            <i class="fas fa-percentage text-info me-1"></i>
                            {{ __('join.score_percentage', ['score' => number_format($scorePercentage, 2)]) }}
                        </p>

                        @if($scorePercentage >= $quiz->pass_percentage)
                            <div class="status-pill pass">
                                <i class="fas fa-thumbs-up"></i>
                                <span>
                                    {{ __('join.passed', ['score' => number_format($scorePercentage, 2), 'required' => $quiz->pass_percentage]) }}
                                </span>
                            </div>
                        @else
                            <div class="status-pill fail">
                                <i class="fas fa-times-circle"></i>
                                <span>
                                    {{ __('join.failed', ['score' => number_format($scorePercentage, 2), 'required' => $quiz->pass_percentage]) }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="actions-row d-flex flex-column flex-md-row justify-content-center align-items-center gap-3">

            @unless($isLearningModeResult)
                @if($attempt->student_code === '0000')
                    <div class="text-center">
                        <button class="btn btn-outline-secondary" disabled>
                            <i class="fas fa-file-pdf me-1"></i> {{ __('join.pdf_unavailable') }}
                        </button>
                        <p class="result-note">{{ __('join.pdf_note') }}</p>
                    </div>
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
