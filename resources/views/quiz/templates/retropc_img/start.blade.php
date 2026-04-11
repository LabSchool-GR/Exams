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
    @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@600&display=swap');

    body {
        background-color: #000000;
        background-image: 
            linear-gradient(0deg, rgba(0, 255, 255, 0.07) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 255, 255, 0.07) 1px, transparent 1px);
        background-size: 40px 40px;
        background-attachment: fixed;
        font-family: 'Orbitron', sans-serif;
        color: #ffffff;
    }

    .overlay {
        position: fixed;
        inset: 0;
        background: radial-gradient(circle at center, rgba(0, 255, 255, 0.04), rgba(0, 0, 0, 0.9));
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

    h2, h4 {
        color: #00fff7;
        text-shadow: 0 0 4px #00fff7;
        text-transform: uppercase;
        letter-spacing: 2px;
    }

    .text-muted {
        color: #80d4ff !important;
        font-size: 0.95rem;
        letter-spacing: 1px;
    }

    .display-3 {
        font-size: 4rem;
        color: #00fff7;
        text-shadow:
            0 0 6px #00fff7,
            0 0 10px #00fff7;
        animation: flicker 1.2s infinite;
    }

    @keyframes flicker {
        0%, 18%, 22%, 25%, 53%, 57%, 100% {
            opacity: 1;
        }
        20%, 24%, 55% {
            opacity: 0.5;
        }
    }

    .fade-in {
        opacity: 0;
        animation: fadeIn 1.6s ease-in-out forwards;
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

        .display-3 {
            font-size: 2.8rem;
        }

        h2, h4 {
            font-size: 1.1rem;
        }
    }
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100 text-center" style="max-width: 600px;">
        <h2 class="fw-bold fade-in" style="animation-delay: 0.5s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="text-muted fade-in mt-2" style="animation-delay: 0.8s;">
            {{ __('join.for_quiz') }}
        </p>

        <h4 class="fw-bold fade-in" style="animation-delay: 1.1s;">
            {{ $quiz->title }}
        </h4>

        <p class="fs-5 text-muted fade-in mt-3" style="animation-delay: 1.4s;">
            <i class="fas fa-user-graduate me-1"></i> {{ __('join.student') }}
            <strong>{{ session('student_name') }}</strong>
        </p>

        <div class="my-4 fade-in" style="animation-delay: 1.7s;">
            <h1 class="fw-bold display-3" id="countdown" data-countdown-redirect="{{ route('quiz.start_question', ['quizKey' => $quizRouteKey]) }}" data-countdown-initial="10" data-countdown-label="{{ __('join.starting_in', ['seconds' => ':seconds']) }}">
                {{ __('join.starting_in', ['seconds' => 10]) }}
            </h1>
        </div>

        <p class="text-muted fade-in" style="animation-delay: 2s;">
            <i class="fas fa-brain me-1"></i> {{ __('join.get_ready') }}
        </p>
    </div>
</div>

@endsection
