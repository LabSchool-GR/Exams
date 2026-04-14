@extends('layouts.quiz_guest')

@section('meta')
    <meta property="og:title" content="{{ $quiz->title }}">
    <meta property="og:description" content="{{ Str::limit($quiz->description, 150) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    @if ($quiz->image)
        <meta property="og:image" content="{{ asset('storage/' . $quiz->image) }}">
        <meta name="twitter:image" content="{{ asset('storage/' . $quiz->image) }}">
    @else
        <meta property="og:image" content="{{ asset('storage/bg-quiz.jpg') }}">
        <meta name="twitter:image" content="{{ asset('storage/bg-quiz.jpg') }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $quiz->title }}">
    <meta name="twitter:description" content="{{ Str::limit($quiz->description, 150) }}">
@endsection

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
    padding: clamp(1rem, 2vh, 2rem) 1rem;
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
    padding: 1.85rem 2rem;
}

.hero-grid {
    display: grid;
    gap: 0.95rem;
}

.hero-stack {
    display: grid;
    justify-items: center;
    gap: 0.8rem;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.42rem 0.8rem;
    border-radius: 999px;
    background: var(--quiz-accent-soft);
    color: var(--quiz-accent);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.app-title {
    margin: 0.1rem auto 0;
    max-width: 20ch;
    font-size: clamp(1.28rem, 1.08rem + 0.7vw, 1.8rem);
    font-weight: 700;
    color: var(--quiz-ink);
    line-height: 1.16;
}

.helper-line {
    margin: 0.2rem 0 0;
    color: var(--quiz-muted);
    font-size: 0.93rem;
}

.quiz-title {
    margin: 0.7rem 0 0;
    font-size: clamp(1rem, 0.9rem + 0.7vw, 1.18rem);
    font-weight: 700;
    color: #213547;
}

.student-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    margin-top: 0.1rem;
    padding: 0.62rem 0.92rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.74);
    border: 1px solid rgba(23, 50, 77, 0.08);
    color: var(--quiz-muted);
}

.student-pill strong {
    color: var(--quiz-ink);
}

.countdown-wrap {
    margin-top: 0.05rem;
}

.countdown-stage {
    display: grid;
    justify-items: center;
    gap: 0.7rem;
}

.countdown-orb {
    position: relative;
    width: 116px;
    height: 116px;
    display: grid;
    place-items: center;
    border-radius: 50%;
    background:
        radial-gradient(circle at 30% 30%, rgba(255, 255, 255, 0.95), rgba(232, 242, 247, 0.95)),
        linear-gradient(145deg, rgba(31, 122, 140, 0.1), rgba(66, 133, 244, 0.14));
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.82),
        0 20px 36px rgba(31, 122, 140, 0.16);
}

.countdown-orb::before {
    content: "";
    position: absolute;
    inset: -16px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(31, 122, 140, 0.12), rgba(31, 122, 140, 0));
    z-index: -1;
}

.countdown-ring {
    position: absolute;
    inset: 8px;
    width: calc(100% - 16px);
    height: calc(100% - 16px);
    transform: rotate(-90deg);
}

.countdown-ring__track,
.countdown-ring__progress {
    fill: none;
    stroke-width: 6;
}

.countdown-ring__track {
    stroke: rgba(31, 122, 140, 0.14);
}

.countdown-ring__progress {
    stroke: url(#countdownGradient);
    stroke-linecap: round;
    transition: stroke-dashoffset 0.9s ease;
}

.countdown-core {
    display: grid;
    justify-items: center;
    line-height: 1;
}

.countdown-value {
    font-size: 1.82rem;
    font-weight: 800;
    color: var(--quiz-ink);
    font-variant-numeric: tabular-nums;
}

.countdown-unit {
    margin-top: 0.28rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--quiz-muted);
}

.ready-line {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin: 0;
    padding: 0.32rem 0;
    color: var(--quiz-muted);
    font-size: 0.9rem;
    animation: pulseReady 1.8s ease-in-out infinite;
    white-space: nowrap;
}

@keyframes pulseReady {
    0%,
    100% {
        opacity: 0.82;
    }
    50% {
        opacity: 1;
    }
}

@media (max-height: 820px) {
    .hero-grid {
        gap: 0.85rem;
    }

    .student-pill {
        padding: 0.6rem 0.85rem;
    }
}

@media (max-height: 720px) {
    .eyebrow {
        padding: 0.38rem 0.72rem;
        font-size: 0.72rem;
    }

    .app-title {
        margin-top: 0.45rem;
        margin-bottom: 0.2rem;
    }

    .helper-line,
    .ready-line {
        font-size: 0.89rem;
    }

    .countdown-wrap {
        margin-top: 0.4rem;
    }

    .countdown-orb {
        width: 104px;
        height: 104px;
    }

    .countdown-value {
        font-size: 1.58rem;
    }

}

.fade-in {
    opacity: 0;
    animation: riseIn 0.7s ease-out forwards;
}

@keyframes riseIn {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
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

    .app-title {
        max-width: 18ch;
        font-size: 1.42rem;
    }

    .countdown-orb {
        width: 102px;
        height: 102px;
    }

    .countdown-value {
        font-size: 1.55rem;
    }

    .ready-line {
        white-space: normal;
        max-width: 26ch;
    }

}
</style>

<div class="overlay"></div>

<div class="container screen-shell d-flex justify-content-center align-items-center">
    <div class="exam-card">
        <div class="exam-card__inner text-center">
            <div class="hero-grid">
                <div class="hero-stack">
                    <div class="eyebrow fade-in" style="animation-delay: 0.2s;">
                        <span>{{ __('join.app_title') }}</span>
                    </div>

                    <p class="helper-line fade-in" style="animation-delay: 0.5s;">
                        {{ __('join.for_quiz') }}
                    </p>

                    <h1 class="app-title fade-in" style="animation-delay: 0.35s;">
                        {{ $quiz->title }}
                    </h1>

                    <div class="student-pill fade-in" style="animation-delay: 0.65s;">
                        <span>{{ __('join.student') }} <strong>{{ session('student_name') }}</strong></span>
                    </div>

                    <div class="countdown-wrap fade-in" style="animation-delay: 0.95s;">
                        <div class="countdown-stage"
                             data-countdown-redirect="{{ route('quiz.start_question', ['quizKey' => $quizRouteKey]) }}"
                             data-countdown-initial="10"
                             data-countdown-label="{{ __('join.starting_shortly_in', ['seconds' => ':seconds']) }}">
                            <div class="countdown-orb" aria-hidden="true">
                                <svg class="countdown-ring" viewBox="0 0 100 100">
                                    <defs>
                                        <linearGradient id="countdownGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#1f7a8c"></stop>
                                            <stop offset="100%" stop-color="#4285f4"></stop>
                                        </linearGradient>
                                    </defs>
                                    <circle class="countdown-ring__track" cx="50" cy="50" r="42"></circle>
                                    <circle class="countdown-ring__progress"
                                            cx="50"
                                            cy="50"
                                            r="42"
                                            data-countdown-progress
                                            data-countdown-radius="42"></circle>
                                </svg>
                                <div class="countdown-core">
                                    <span class="countdown-value" data-countdown-value>10</span>
                                    <span class="countdown-unit">{{ __('join.countdown_unit') }}</span>
                                </div>
                            </div>

                            <span class="visually-hidden" id="countdown" data-countdown-text>
                                {{ __('join.starting_shortly_in', ['seconds' => 10]) }}
                            </span>
                        </div>
                    </div>

                    <p class="ready-line fade-in" style="animation-delay: 1.1s;">
                        {{ __('join.get_ready') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Redirect automatically after the countdown so all templates start the attempt consistently. --}}
@endsection
