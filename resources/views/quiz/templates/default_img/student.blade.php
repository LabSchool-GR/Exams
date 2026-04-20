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
    min-height: 100%;
    width: 100%;
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
    gap: 1rem;
    align-items: start;
}

.hero-media {
    display: block;
    overflow: hidden;
    border-radius: 1rem;
    border: 1px solid rgba(23, 50, 77, 0.08);
    background: linear-gradient(160deg, rgba(31, 122, 140, 0.08), rgba(255, 255, 255, 0.96));
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6);
}

.hero-media img {
    display: block;
    width: 100%;
    max-height: 190px;
    object-fit: cover;
}

.eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.45rem 0.85rem;
    border-radius: 999px;
    background: var(--quiz-accent-soft);
    color: var(--quiz-accent);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.app-title {
    margin: 0.45rem 0 0;
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--quiz-ink);
}

.helper-line {
    margin: 0;
    color: var(--quiz-muted);
    font-size: 0.95rem;
}

.quiz-title {
    margin: 0.7rem 0 0;
    font-size: clamp(1rem, 0.9rem + 0.7vw, 1.18rem);
    font-weight: 700;
    color: #213547;
}

.description-box,
.error-box,
.access-box {
    margin-top: 0.85rem;
    padding: 0.85rem 0.95rem;
    border-radius: 0.95rem;
    border: 1px solid rgba(23, 50, 77, 0.08);
}

.description-box {
    background: rgba(255, 255, 255, 0.76);
    color: #31485d;
    white-space: pre-line;
}

.error-box {
    background: rgba(220, 38, 38, 0.08);
    color: #991b1b;
    border-color: rgba(220, 38, 38, 0.14);
}

.access-box {
    background: linear-gradient(135deg, rgba(31, 122, 140, 0.08), rgba(66, 133, 244, 0.08));
    color: var(--quiz-muted);
}

.form-label {
    color: var(--quiz-ink);
    font-weight: 600;
}

.input-shell {
    border-radius: 1rem;
    border: 1px solid rgba(23, 50, 77, 0.12);
    background: rgba(255, 255, 255, 0.9);
    overflow: hidden;
    box-shadow: 0 16px 30px rgba(15, 23, 42, 0.08);
}

.input-shell .form-control,
.input-shell .btn {
    border: 0;
    min-height: 3rem;
}

.input-shell .form-control {
    background: transparent;
    color: var(--quiz-ink);
    letter-spacing: 0.22em;
    font-size: 1.05rem;
}

.input-shell .form-control::placeholder {
    letter-spacing: normal;
    color: #8a9aad;
}

.input-shell .btn {
    background: rgba(23, 50, 77, 0.04);
    color: var(--quiz-muted);
}

.btn-start {
    width: 100%;
    margin-top: 0.9rem;
    border: 0;
    border-radius: 999px;
    padding: 0.82rem 1rem;
    background: linear-gradient(135deg, #1f7a8c, #2a9d8f);
    color: #fff;
    font-weight: 700;
    box-shadow: 0 18px 30px rgba(31, 122, 140, 0.22);
}

.btn-start:hover,
.btn-start:focus {
    color: #fff;
    filter: brightness(1.02);
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

@media (min-width: 768px) {
    .hero-grid {
        grid-template-columns: minmax(0, 0.95fr) minmax(0, 1.05fr);
    }
}

@media (min-width: 768px) {
    .hero-grid--single {
        grid-template-columns: 1fr;
    }
}

@media (max-height: 820px) {
    .hero-media img {
        max-height: min(17dvh, 145px);
    }

    .hero-grid {
        gap: 0.95rem;
    }

    .description-box,
    .error-box,
    .access-box {
        font-size: 0.92rem;
    }
}

@media (max-height: 720px) {
    .eyebrow {
        padding: 0.38rem 0.72rem;
        font-size: 0.72rem;
    }

    .helper-line,
    .description-box,
    .error-box,
    .access-box {
        font-size: 0.88rem;
    }

    .input-shell .form-control,
    .input-shell .btn {
        min-height: 2.7rem;
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
        font-size: 1.12rem;
    }
}
</style>

<div class="overlay"></div>

<div class="container screen-shell d-flex justify-content-center align-items-center">
    <div class="exam-card" data-focus-on-load="student_code">
        <div class="exam-card__inner">
            <div class="hero-grid hero-grid--single">
                <div>
                    @if($quiz->image)
                        <div class="hero-media fade-in" style="animation-delay: 0.1s;">
                            <img src="{{ asset('storage/' . $quiz->image) }}" alt="{{ $quiz->title }}">
                        </div>
                    @endif

                    <div class="access-box fade-in text-start" style="animation-delay: 0.24s;">
                        <strong><i class="fas fa-lock me-1"></i> {{ __('join.student_code_label') }}</strong><br>
                        {{ __('join.for_quiz') }}
                    </div>
                </div>

                <div>
                    <div class="eyebrow fade-in" style="animation-delay: 0.14s;">
                        <i class="fas fa-graduation-cap"></i>
                        <span>{{ __('join.app_title') }}</span>
                    </div>

                    <p class="helper-line fade-in" style="animation-delay: 0.38s;">
                        {{ __('join.for_quiz') }}
                    </p>

                    <h1 class="app-title fade-in" style="animation-delay: 0.26s;">
                        {{ $quiz->title }}
                    </h1>

                    @if($quiz->description)
                        <div class="description-box fade-in" style="animation-delay: 0.5s;">
                            <strong><i class="fas fa-info-circle me-1"></i> {{ __('join.description_label') }}</strong><br>
                            {{ $quiz->description }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="error-box fade-in" style="animation-delay: 0.62s;">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('quiz.validate_student') }}"
                          method="POST"
                          class="mt-4 fade-in"
                          style="animation-delay: 0.74s;">
                        @csrf

                        <div class="mb-3">
                            <label for="student_code" class="form-label">
                                <i class="fas fa-key me-1"></i> {{ __('join.student_code_label') }}
                            </label>
                            <div class="input-group input-shell">
                                <input type="password"
                                       name="student_code"
                                       id="student_code"
                                       required
                                       maxlength="4"
                                       class="form-control text-center"
                                       placeholder="{{ __('join.placeholder_code') }}">
                                <button class="btn" type="button" id="toggle-password" data-password-toggle data-password-toggle-target="student_code">
                                    <i class="fas fa-eye" id="toggle-icon" data-password-toggle-icon></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-start">
                            <i class="fas fa-rocket me-1"></i> {{ __('join.start_quiz') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Keep the student-code reveal toggle explicit for shared kiosk or classroom devices. --}}
@endsection
