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
    --exam-bg: #f7f2ea;
    --exam-paper: #ffffff;
    --exam-primary: #26436b;
    --exam-accent: #3b82f6;
    --exam-muted: #7b7f8e;
    --exam-border-soft: rgba(31, 41, 55, 0.08);
}

/* ============================
   BODY + BACKGROUND IMAGE
   ============================ */
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



/* ============================
   LAYOUT STRUCTURE
   ============================ */
.container {
    position: relative;
    z-index: 2;
    min-height: 100vh;
}

.card-glass {
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.96), rgba(249, 250, 252, 0.98));
    border-radius: 1.4rem;
    padding: 1.9rem 2.1rem;
    border: 1px solid var(--exam-border-soft);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
}

/* ============================
   FADE-IN ANIMATION
   ============================ */
.fade-in {
    opacity: 0;
    animation: fadeIn 0.9s ease-out forwards;
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(12px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============================
   TYPOGRAPHY + HEADERS
   ============================ */

.exam-header-small {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--exam-muted);
    margin-bottom: 0.7rem;
}

.app-title {
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--exam-primary);
    margin-top: 0.4rem;
    margin-bottom: 0.4rem;
}

.quiz-title {
    font-weight: 600;
    font-size: 1.05rem;
    color: #374151;
    margin-top: 0.8rem;
    margin-bottom: 0.8rem;
}

.student-line {
    font-size: 0.95rem;
    color: var(--exam-muted);
}

.student-line strong {
    color: var(--exam-primary);
}

/* ============================
   COUNTDOWN
   ============================ */

.countdown-wrapper {
    margin-top: 1.4rem;
    margin-bottom: 1rem;
    display: flex;
    justify-content: center;
}

.countdown-pill {
    padding: 0.6rem 1.4rem;
    border-radius: 999px;
    border: 1px solid rgba(59, 130, 246, 0.35);
    background: #eef2ff;
    box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
}

#countdown {
    margin: 0;
    font-size: 1.05rem;
    font-weight: 600;
    color: var(--exam-primary);
}

.get-ready {
    font-size: 0.92rem;
    margin-top: 0.8rem;
    color: var(--exam-muted);
}

/* ============================
   RESPONSIVE
   ============================ */

@media (max-width: 576px) {
    .card-glass {
        padding: 1.6rem 1.4rem;
        border-radius: 1.1rem;
    }

    .app-title {
        font-size: 1.1rem;
    }

    .quiz-title {
        font-size: 1rem;
    }

    #countdown {
        font-size: 0.98rem;
    }
}
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center">
    <div class="card card-glass shadow-lg w-100 text-center" style="max-width: 640px;">
        <div class="exam-header-small fade-in" style="animation-delay: 0.2s;">
            {{ __('join.uni_module_header') }}
        </div>

        <h2 class="app-title fade-in" style="animation-delay: 0.4s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="text-muted fade-in mt-1" style="animation-delay: 0.6s;">
            {{ __('join.for_quiz') }}
        </p>

        <h4 class="quiz-title fade-in" style="animation-delay: 0.9s;">
            {{ $quiz->title }}
        </h4>

        <p class="student-line fade-in mt-3" style="animation-delay: 1.1s;">
            <i class="fas fa-user-graduate me-1"></i> {{ __('join.student') }}
            <strong>{{ session('student_name') }}</strong>
        </p>

        <div class="countdown-wrapper fade-in" style="animation-delay: 1.4s;">
            <div class="countdown-pill">
                <p id="countdown" data-countdown-redirect="{{ route('quiz.start_question', ['quizKey' => $quizRouteKey]) }}" data-countdown-initial="10" data-countdown-label="{{ __('join.starting_in', ['seconds' => ':seconds']) }}">
                    {{ __('join.starting_in', ['seconds' => 10]) }}
                </p>
            </div>
        </div>

        <p class="get-ready fade-in mt-2" style="animation-delay: 1.8s;">
            <i class="fas fa-brain me-1"></i> {{ __('join.get_ready') }}
        </p>
    </div>
</div>

{{-- Redirect automatically after the countdown so all templates start the attempt consistently. --}}
@endsection
