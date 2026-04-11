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

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.5rem;
        }
    }
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg w-100 text-center" style="max-width: 600px;">
        <h2 class="text-primary fw-bold fade-in" style="animation-delay: 0.5s;">
            <i class="fas fa-graduation-cap me-2"></i> {{ __('join.app_title') }}
        </h2>

        <p class="text-muted fade-in mt-2" style="animation-delay: 0.8s;">
            {{ __('join.for_quiz') }}
        </p>

        <h4 class="fw-bold text-secondary fade-in" style="animation-delay: 1.1s;">
            {{ $quiz->title }}
        </h4>

        <p class="fs-5 text-muted fade-in mt-3" style="animation-delay: 1.4s;">
            <i class="fas fa-user-graduate me-1"></i> {{ __('join.student') }}
            <strong>{{ session('student_name') }}</strong>
        </p>

        <div class="my-4 fade-in" style="animation-delay: 1.7s;">
            <h1 class="fw-bold text-danger display-3" id="countdown" data-countdown-redirect="{{ route('quiz.start_question', ['quizKey' => $quizRouteKey]) }}" data-countdown-initial="10" data-countdown-label="{{ __('join.starting_in', ['seconds' => ':seconds']) }}">
                {{ __('join.starting_in', ['seconds' => 10]) }}
            </h1>
        </div>

        <p class="text-muted fade-in" style="animation-delay: 2s;">
            <i class="fas fa-brain me-1"></i> {{ __('join.get_ready') }}
        </p>
    </div>
</div>

{{-- Redirect automatically after the countdown so all templates start the attempt consistently. --}}
@endsection
