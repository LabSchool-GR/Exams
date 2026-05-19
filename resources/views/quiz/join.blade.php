@extends('layouts.guest')

@section('hide_guest_footer', '1')

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

    .typing-text {
        white-space: pre-line;
        overflow: hidden;
        display: inline-block;
        position: relative;
        min-height: 1.5em;
    }

    .typing-text::after {
        content: '';
        display: inline-block;
        width: 2px;
        height: 1.2em;
        background-color: black;
        margin-left: 3px;
        animation: blink 0.8s steps(1) infinite;
        vertical-align: bottom;
    }

    .typing-complete::after {
        display: none !important;
    }

    @keyframes blink {
        50% {
            border-color: transparent;
        }
    }

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }

        .typing-text {
            font-size: 1rem;
        }
    }
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100" style="max-width: 600px;">
        <h2 class="text-center text-primary fw-bold fade-in" style="animation-delay: 0.5s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="mt-3 text-muted text-center typing-text" data-typing-text="{{ __('join.description') }}" data-typing-start-delay="1500" data-typing-delay="50" data-typing-reveal-target="teacher-link" data-typing-reveal-delay="25000"></p>

        {{-- Quiz code entry is the primary participant entry point. --}}
        <form id="quizCodeForm" action="{{ route('quiz.validate_code') }}" method="POST" class="mt-4">
            @csrf
            <label for="quiz_code" class="form-label fw-semibold">
                <i class="fas fa-hashtag me-1"></i> {{ __('join.quiz_code') }}
            </label>
            <input type="text" name="quiz_code" id="quiz_code" maxlength="8" required
                class="form-control text-center fw-bold fs-4 mb-3" placeholder="--------">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-arrow-right me-1"></i> {{ __('join.start_button') }}
            </button>
        </form>

        {{-- Reveal the teacher link after the landing copy has had time to animate. --}}
        <div id="teacher-link" class="text-center mt-4 d-none">
            <a href="{{ route('login') }}" class="btn btn-outline-secondary">
                <i class="fas fa-chalkboard-teacher me-1"></i> {{ __('join.teacher_area') }}
            </a>
        </div>

        {{-- Keep language switching visible before authentication starts. --}}
        <div class="text-end mb-3">
            <br>
            <a href="{{ route('set.locale', ['locale' => 'el']) }}">
                <img src="{{ asset('storage/flags/el.png') }}" alt="Ελληνικά" width="24" class="me-1">
            </a>
            <a href="{{ route('set.locale', ['locale' => 'en']) }}">
                <img src="{{ asset('storage/flags/en.png') }}" alt="English" width="24">
            </a>
        </div>
    </div>
</div>
@endsection
