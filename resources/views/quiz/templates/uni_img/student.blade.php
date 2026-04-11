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

/* =============================
   BODY + BACKGROUND IMAGE
   ============================= */
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

/* =============================
   MAIN LAYOUT
   ============================= */
.overlay {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.9), rgba(249, 250, 252, 0.96));
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

/* =============================
   ANIMATIONS
   ============================= */
.fade-in {
    opacity: 0;
    animation: fadeIn 0.7s ease-out forwards;
    animation-fill-mode: both;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* =============================
   TEXT STYLES
   ============================= */
.exam-header-small {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.16em;
    color: var(--exam-muted);
    text-align: center;
    margin-bottom: 0.6rem;
}

.app-title {
    text-align: center;
    font-weight: 700;
    font-size: 1.25rem;
    color: var(--exam-primary);
    margin-bottom: 0.35rem;
}

.app-subtitle {
    text-align: center;
    font-size: 0.9rem;
    color: var(--exam-muted);
    margin-bottom: 0.7rem;
}

.quiz-title {
    text-align: center;
    font-weight: 600;
    font-size: 1.05rem;
    color: #374151;
    margin-bottom: 0.7rem;
}

/* Description box */
.description-box {
    font-size: 0.94rem;
    border-radius: 1rem;
    border: 1px solid rgba(59, 130, 246, 0.25);
    background: #eef2ff;
    color: #1f2937;
}

/* Error box */
.error-box {
    border-radius: 1rem;
}

/* =============================
   FORM ELEMENTS
   ============================= */
.form-label {
    font-size: 0.9rem;
    color: var(--exam-primary);
}

.input-group .form-control {
    border-radius: 999px 0 0 999px;
    border-color: rgba(148, 163, 184, 0.8);
    font-size: 1rem;
}

.input-group .btn {
    border-radius: 0 999px 999px 0;
    border-color: rgba(148, 163, 184, 0.8);
}

#student_code {
    letter-spacing: 0.2em;
}

.btn-start {
    border-radius: 999px;
    font-size: 0.95rem;
    font-weight: 600;
}

/* =============================
   RESPONSIVE
   ============================= */
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
}
</style>


<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center">
    <div class="card card-glass shadow-lg w-100"
         style="max-width: 600px;" data-focus-on-load="student_code">

        <div class="exam-header-small fade-in" style="animation-delay: 0.1s;">
            {{ __('join.uni_module_header') }}
        </div>

        <h2 class="app-title fade-in" style="animation-delay: 0.3s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="app-subtitle fade-in" style="animation-delay: 0.45s;">
            {{ __('join.for_quiz') }}
        </p>

        <h4 class="quiz-title fade-in" style="animation-delay: 0.6s;">
            {{ $quiz->title }}
        </h4>

        @if($quiz->description)
            <div class="description-box mt-2 px-3 py-2 fade-in" style="animation-delay: 0.8s;">
                <strong><i class="fas fa-info-circle me-1"></i> {{ __('join.description_label') }}</strong><br>
                {{ $quiz->description }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center mt-3 error-box fade-in" style="animation-delay: 1s;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('quiz.validate_student') }}"
              method="POST"
              class="mt-4 fade-in"
              style="animation-delay: 1.2s;">
            @csrf

            <div class="mb-3">
                <label for="student_code" class="form-label fw-semibold">
                    <i class="fas fa-key me-1"></i> {{ __('join.student_code_label') }}
                </label>
                <div class="input-group shadow-sm">
                    <input type="password"
                           name="student_code"
                           id="student_code"
                           required
                           maxlength="4"
                           class="form-control text-center"
                           placeholder="{{ __('join.placeholder_code') }}">
                    <button class="btn btn-outline-secondary" type="button" id="toggle-password" data-password-toggle data-password-toggle-target="student_code">
                        <i class="fas fa-eye" id="toggle-icon" data-password-toggle-icon></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 btn-start mt-3">
                <i class="fas fa-rocket me-1"></i> {{ __('join.start_quiz') }}
            </button>
        </form>
    </div>
</div>

{{-- Keep the student-code reveal toggle explicit for shared kiosk or classroom devices. --}}
@endsection
