@extends('layouts.quiz_guest')

@section('content')
<style>
    body {
        background-image: url('{{ asset('storage/exakoustou.png') }}');
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

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }
    }
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100" style="max-width: 600px;" data-focus-on-load="student_code">
        <h2 class="text-center text-primary fw-bold fade-in" style="animation-delay: 0.5s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="text-muted text-center mb-2 fade-in" style="animation-delay: 0.8s;">
            {{ __('join.for_quiz') }}
        </p>

        <h4 class="fw-bold text-secondary text-center fade-in" style="animation-delay: 1.1s;">
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

{{-- Keep the student-code reveal toggle explicit for shared kiosk or classroom devices. --}}
@endsection
