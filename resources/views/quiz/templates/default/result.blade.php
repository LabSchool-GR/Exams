@extends('layouts.quiz_guest')

@section('content')
<style>
:root {
    --quiz-bg: #edf3f7;
    --quiz-paper: rgba(255, 255, 255, 0.94);
    --quiz-ink: #17324d;
    --quiz-muted: #5e7389;
    --quiz-accent: #1f7a8c;
    --quiz-accent-soft: rgba(31, 122, 140, 0.12);
    --quiz-border: rgba(23, 50, 77, 0.12);
    --quiz-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    --quiz-success: #177245;
    --quiz-success-soft: rgba(23, 114, 69, 0.12);
    --quiz-fail: #b42318;
    --quiz-fail-soft: rgba(180, 35, 24, 0.1);
}

body {
    min-height: 100dvh;
    @if(isset($quiz) && $quiz->image)
        background-image: url('{{ asset('storage/' . $quiz->image) }}');
    @else
        background-image: url('{{ asset('storage/bg-quiz.jpg') }}');
    @endif
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
    min-height: 100%;
    width: 100%;
    padding: clamp(1rem, 2vh, 2rem) 1rem;
}

.exam-card {
    width: 100%;
    max-width: 760px;
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.94);
    box-shadow: 0 25px 50px rgba(15, 23, 42, 0.15);
    backdrop-filter: blur(12px);
    overflow: hidden;
}

.exam-card__inner {
    padding: 1.85rem 2rem;
}

.hero-media {
    margin-bottom: 1rem;
    overflow: hidden;
    border-radius: 1.35rem;
    border: 4px solid #ffffff;
    box-shadow: 0 12px 25px rgba(15, 23, 42, 0.08);
}

.hero-media img {
    display: block;
    width: 100%;
    max-height: min(24dvh, 220px);
    object-fit: cover;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    background: var(--quiz-accent-soft);
    color: var(--quiz-accent);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.result-title {
    margin: 0.9rem 0 0.4rem;
    font-size: clamp(1.1rem, 1rem + 0.95vw, 1.45rem);
    font-weight: 800;
    color: var(--quiz-ink);
}

.result-subtitle {
    margin: 0;
    color: var(--quiz-muted);
    font-size: 1rem;
}

.identity-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.2rem;
    padding: 0.55rem 0.95rem;
    border-radius: 999px;
    background: #ffffff;
    border: 1px solid rgba(23, 50, 77, 0.08);
    color: var(--quiz-muted);
    font-size: 0.93rem;
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.04);
}

.identity-chip strong {
    color: var(--quiz-ink);
    font-weight: 700;
}

.status-pill {
    display: inline-block;
    margin-top: 1rem;
    padding: 0.8rem 1.4rem;
    border-radius: 999px;
    font-weight: 800;
    font-size: 1rem;
    text-align: center;
    box-shadow: 0 8px 20px rgba(0,0,0,0.04);
}

.status-pill.pass {
    background: var(--quiz-success-soft);
    color: var(--quiz-success);
    border: 1px solid rgba(23, 114, 69, 0.22);
}

.status-pill.fail {
    background: var(--quiz-fail-soft);
    color: var(--quiz-fail);
    border: 1px solid rgba(180, 35, 24, 0.18);
}

.status-pill.learning {
    background: rgba(31, 122, 140, 0.1);
    color: var(--quiz-accent);
    border: 1px solid rgba(31, 122, 140, 0.16);
}

.metrics-panel {
    display: grid;
    gap: 0;
    margin-top: 1.5rem;
    border-radius: 1.2rem;
    background: #ffffff;
    border: 1px solid rgba(23, 50, 77, 0.08);
    box-shadow: 0 15px 35px rgba(15, 23, 42, 0.05);
    overflow: hidden;
}

.metric-item {
    padding: 1rem 1.1rem;
    text-align: left;
}

.metric-item + .metric-item {
    border-top: 1px solid rgba(23, 50, 77, 0.08);
}

.metric-label {
    display: block;
    margin-bottom: 0.45rem;
    color: var(--quiz-muted);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.metric-value {
    color: var(--quiz-accent);
    font-size: 1.8rem;
    font-weight: 800;
    line-height: 1.1;
}

.metric-detail {
    margin-top: 0.45rem;
    color: var(--quiz-muted);
    font-size: 0.88rem;
    line-height: 1.4;
}

.result-note {
    margin-top: 0.7rem;
    color: var(--quiz-muted);
    font-size: 0.88rem;
}

.actions-grid {
    display: grid;
    gap: 0.75rem;
    margin-top: 1.25rem;
}

.btn-action {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 0.35rem;
    width: 100%;
    border-radius: 999px;
    padding: 0.88rem 1rem;
    font-weight: 700;
    font-size: 0.98rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

@media (max-height: 820px) {
    .hero-media img {
        max-height: min(17dvh, 145px);
    }

    .metrics-grid {
        gap: 0.75rem;
    }

    .metric-value {
        font-size: 1rem;
    }
}

@media (max-height: 720px) {
    .eyebrow {
        padding: 0.38rem 0.72rem;
        font-size: 0.72rem;
    }

    .result-subtitle,
    .result-note {
        font-size: 0.88rem;
    }

    .status-pill {
        font-size: 0.84rem;
    }
}

.btn-primary-soft {
    background: linear-gradient(135deg, #1f7a8c, #2a9d8f);
    color: #fff;
    border: 0;
    box-shadow: 0 18px 30px rgba(31, 122, 140, 0.22);
}

.btn-primary-soft:hover,
.btn-primary-soft:focus {
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 22px 36px rgba(31, 122, 140, 0.3);
}

.btn-outline-soft {
    background: rgba(255, 255, 255, 0.62);
    color: var(--quiz-ink);
    border: 1px solid rgba(23, 50, 77, 0.14);
}

.btn-outline-soft:hover {
    background: rgba(255, 255, 255, 1);
    box-shadow: 0 8px 16px rgba(15, 23, 42, 0.05);
}

.btn-warning-soft {
    background: rgba(183, 121, 31, 0.12);
    color: #8a5a12;
    border: 1px solid rgba(183, 121, 31, 0.22);
}

.btn-warning-soft:hover {
    background: rgba(183, 121, 31, 0.18);
}

.btn-disabled-soft {
    background: linear-gradient(180deg, rgba(246, 248, 250, 0.96), rgba(237, 242, 247, 0.96));
    color: rgba(23, 50, 77, 0.58);
    border: 1px solid rgba(23, 50, 77, 0.14);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    cursor: not-allowed;
    opacity: 1;
}

.btn-disabled-soft i {
    color: rgba(23, 50, 77, 0.46);
}

@media (min-width: 640px) {
    .metrics-panel {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .metric-item + .metric-item {
        border-top: 0;
        border-left: 1px solid rgba(23, 50, 77, 0.08);
    }
}

@media (min-width: 640px) and (max-width: 899px) {
    .actions-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: start;
    }
}

@media (min-width: 900px) {
    .actions-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        align-items: start;
    }
}

@media (max-width: 576px) {
    .screen-shell {
        padding: 1rem 0.75rem;
    }

    .exam-card {
        border-radius: 1rem;
    }

    .exam-card__inner {
        padding: 1.3rem;
    }

    .result-title {
        font-size: 1.25rem;
    }
}
</style>

@php
    $isLearningModeResult = $isLearningModeResult ?? false;
    $passedQuiz = !$isLearningModeResult && $scorePercentage >= $quiz->pass_percentage;
    $roundedScorePercentage = (int) round($scorePercentage);
    $isGuestAttempt = $attempt->student_code === '0000';
    $participantRoleLabel = $isGuestAttempt ? __('join.guest_name') : rtrim(__('join.student'), ':');
    $correctAnswersLabel = \Illuminate\Support\Str::before(__('join.correct_answers', ['correct' => 0, 'total' => 0]), ': 0');
    $scorePercentageLabel = \Illuminate\Support\Str::before(__('join.score_percentage', ['score' => 0]), ': 0');
@endphp

<div class="overlay"></div>

<div class="container screen-shell d-flex justify-content-center align-items-center">
    <div class="exam-card">
        <div class="exam-card__inner text-center">
            @if($quiz->image)
                <div class="hero-media">
                    <img src="{{ asset('storage/' . $quiz->image) }}" alt="{{ $quiz->title }}">
                </div>
            @endif

            <div class="eyebrow">
                <span>{{ __('join.results') }}</span>
            </div>

            <h1 class="result-title">{{ __('join.quiz_completed') }}</h1>
            <div class="identity-chip">
                <span>{{ $participantRoleLabel }}: <strong>{{ $attempt->student_name }}</strong></span>
            </div>

            @if($isLearningModeResult)
                <div class="status-pill learning">
                    <span>{{ __('join.learning_mode_result_message') }}</span>
                </div>
            @elseif($passedQuiz)
                <div class="status-pill pass">
                    <span>{{ __('join.passed', ['score' => $roundedScorePercentage, 'required' => $quiz->pass_percentage]) }}</span>
                </div>
            @else
                <div class="status-pill fail">
                    <span>{{ __('join.failed', ['score' => $roundedScorePercentage, 'required' => $quiz->pass_percentage]) }}</span>
                </div>
            @endif

            @unless($isLearningModeResult)
                <div class="metrics-panel">
                    <div class="metric-item">
                        <span class="metric-label">{{ $correctAnswersLabel }}</span>
                        <div class="metric-value">{{ $correctCount }} / {{ $totalQuestions }}</div>
                        <div class="metric-detail">{{ __('join.total_questions_note', ['total' => $totalQuestions]) }}</div>
                    </div>

                    <div class="metric-item">
                        <span class="metric-label">{{ $scorePercentageLabel }}</span>
                        <div class="metric-value">{{ $roundedScorePercentage }}%</div>
                        <div class="metric-detail">{{ __('join.pass_threshold_note', ['required' => (int) $quiz->pass_percentage]) }}</div>
                    </div>
                </div>
            @else
                <p class="result-note">{{ __('join.learning_mode_result_message') }}</p>
            @endunless

            <div class="actions-grid">
                @unless($isLearningModeResult)
                    @if($isGuestAttempt)
                        <div>
                            <button class="btn btn-action btn-disabled-soft" disabled>
                                {{ __('join.pdf_unavailable') }}
                            </button>
                            <p class="result-note">{{ __('join.pdf_note') }}</p>
                        </div>
                    @else
                        <a href="{{ URL::temporarySignedRoute('quiz_attempts.download_pdf_signed', now()->addMinutes((int) config('security.signed_urls.attempt_pdf_ttl_minutes', 1440)), [$quiz, $attempt]) }}"
                           class="btn btn-action btn-primary-soft">
                            {{ __('join.download_pdf') }}
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

                    @if($retryUrl)
                        <a href="{{ $retryUrl }}" class="btn btn-action btn-warning-soft">
                            {{ __('join.retry_quiz') }}
                        </a>
                    @endif
                @endunless

                <a href="{{ route('quiz.join') }}" class="btn btn-action btn-outline-soft">
                    {{ __('join.back_to_home') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
