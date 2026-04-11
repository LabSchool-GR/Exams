@extends('layouts.quiz_guest')

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
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(6px);
        border-radius: 1rem;
        padding: 2rem;
        max-width: 640px;
        width: 100%;
    }

    .info-box {
        border-radius: 0.9rem;
        background: rgba(14, 116, 144, 0.08);
        border: 1px solid rgba(14, 116, 144, 0.14);
        padding: 0.9rem 1rem;
    }

    @media (max-width: 576px) {
        .card-glass {
            padding: 1.35rem;
        }
    }
</style>

<div class="overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card card-glass shadow-lg">
        <div class="text-center mb-4">
            <h2 class="text-primary fw-bold mb-3">
                <i class="fas fa-exclamation-triangle me-2"></i>{{ __('join.active_quiz_conflict_title') }}
            </h2>
            <p class="text-muted mb-0">{{ __('join.active_quiz_conflict_message') }}</p>
        </div>

        @if($quiz)
            <div class="info-box mb-3">
                <div class="fw-semibold text-primary mb-1">{{ __('join.active_quiz_conflict_current', ['title' => $quiz->title]) }}</div>

                @if(!empty($studentName))
                    <div class="text-muted">{{ __('join.active_quiz_conflict_participant', ['name' => $studentName]) }}</div>
                @endif
            </div>
        @endif

        <p class="small text-muted mb-4">{{ __('join.active_quiz_conflict_tip') }}</p>

        <div class="d-grid gap-2">
            @if($canContinue)
                <a href="{{ route('quiz.start') }}" class="btn btn-primary">
                    <i class="fas fa-play me-1"></i> {{ __('join.continue_active_quiz') }}
                </a>
            @endif

            <a href="{{ route('quiz.join') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> {{ __('join.back_to_home') }}
            </a>
        </div>
    </div>
</div>
@endsection
