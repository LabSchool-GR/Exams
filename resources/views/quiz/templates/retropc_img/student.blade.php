@extends('layouts.quiz_guest')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap');

    body {
        background-color: #0d0d0d;
        background-image: 
            linear-gradient(0deg, rgba(0, 255, 255, 0.08) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 255, 255, 0.08) 1px, transparent 1px);
        background-size: 40px 40px;
        background-attachment: fixed;
        font-family: 'Orbitron', sans-serif;
        color: #fff;
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: radial-gradient(circle at center, rgba(0, 255, 255, 0.07), rgba(0, 0, 0, 0.85));
        z-index: 1;
    }

    .card-glass {
        background: rgba(0, 0, 0, 0.65);
        border: 2px solid #00fff7;
        box-shadow: 0 0 20px #00fff7 inset;
        backdrop-filter: blur(8px);
        border-radius: 1.5rem;
        padding: 2.5rem;
        z-index: 2;
    }

    h2, h4 {
        color: #00fff7;
        text-shadow: 0 0 4px #00fff7;
        text-transform: uppercase;
        letter-spacing: 2px;
        font-weight: bold;
    }

    .text-muted {
        color: #a0fdfd !important;
        font-size: 0.95rem;
        letter-spacing: 1px;
    }

    .alert-info {
        background: rgba(0, 255, 255, 0.05);
        border-color: #00fff7;
        color: #00fff7;
    }

    .alert-danger {
        background: rgba(255, 50, 100, 0.08);
        border-color: #ff3366;
        color: #ff3366;
    }

    .input-group input.form-control {
        background-color: #000;
        color: #0ff;
        border: 1px solid #0ff;
        font-weight: bold;
        text-shadow: 0 0 2px #0ff;
    }

    .input-group input::placeholder {
        color: #0ff;
        opacity: 0.6;
    }

    .input-group .btn {
        background: #000;
        border: 1px solid #0ff;
        color: #0ff;
    }

    .btn-success {
        background: linear-gradient(135deg, #00fff7, #00d5ff);
        color: #000;
        font-weight: bold;
        border: none;
        border-radius: 2rem;
        box-shadow: 0 0 12px #00fff7;
        transition: 0.3s ease-in-out;
        font-size: 1rem;
        letter-spacing: 2px;
    }

    .btn-success:hover {
        box-shadow: 0 0 20px #00fff7, 0 0 25px #00f0ff;
        filter: brightness(1.05);
    }

    .fade-in {
        opacity: 0;
        animation: fadeIn 1.8s ease-in-out forwards;
        animation-fill-mode: both;
    }

    @keyframes fadeIn {
        to {
            opacity: 1;
        }
    }

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }

        h2, h4 {
            font-size: 1.1rem;
        }
    }
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100" style="max-width: 600px;" data-focus-on-load="student_code">
        <h2 class="text-center fade-in" style="animation-delay: 0.5s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="text-muted text-center mb-2 fade-in" style="animation-delay: 0.8s;">
            {{ __('join.for_quiz') }}
        </p>

        <h4 class="text-center fade-in" style="animation-delay: 1.1s;">
            {{ $quiz->title }}
        </h4>

        @if($quiz->description)
            <div class="alert alert-info mt-3 text-start fade-in" style="font-size: 0.95rem; animation-delay: 1.4s;">
                <strong><i class="fas fa-info-circle me-1"></i> {{ __('join.description_label') }}</strong><br>
                {{ $quiz->description }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger text-center mt-4 fade-in" style="animation-delay: 1.7s;">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('quiz.validate_student') }}" method="POST" class="mt-4 fade-in" style="animation-delay: 2s;">
            @csrf

            <div class="mb-3">
                <label for="student_code" class="form-label fw-semibold">
                    <i class="fas fa-key me-1"></i> {{ __('join.student_code_label') }}
                </label>
                <div class="input-group shadow-sm">
                    <input type="password" name="student_code" id="student_code" required maxlength="4"
                           class="form-control text-center fs-5" placeholder="{{ __('join.placeholder_code') }}">
                    <button class="btn btn-outline-secondary" type="button" id="toggle-password" data-password-toggle data-password-toggle-target="student_code">
                        <i class="fas fa-eye" id="toggle-icon" data-password-toggle-icon></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-bold mt-3">
                <i class="fas fa-rocket me-1"></i> {{ __('join.start_quiz') }}
            </button>
        </form>
    </div>
</div>

@endsection