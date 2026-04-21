@extends('layouts.quiz_guest')

@section('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Noto+Sans+Display:wght@700;800;900&family=VT323&display=swap" rel="stylesheet">
@endsection

@section('content')
@include('quiz.templates.retroAXD3_img.partials.theme')

<style>
.retro-screen-bar--stop {
    color: var(--retro-red);
    text-shadow: 0 0 10px rgba(255, 100, 106, 0.35);
}

.retro-screen-bar__label--stop {
    gap: 0.65rem;
}

.retro-screen-bar__label--stop::before {
    display: none;
}

.retro-screen-bar__stop-icon {
    width: 11px;
    height: 11px;
    border-radius: 2px;
    background: currentColor;
    box-shadow: 0 0 8px currentColor;
}

.retro-student-screen {
    min-height: var(--retro-stage-min-height);
    display: grid;
    align-content: center;
    gap: 1rem;
}

.retro-student-shell {
    width: min(100%, 60rem);
    margin: 0 auto;
    display: grid;
    gap: 1rem;
}

.retro-student-form {
    width: min(100%, 52rem);
    margin: 0 auto;
}

.retro-student-panel {
    width: min(100%, 60rem);
    margin: 0 auto;
}

.retro-student-panel .retro-note {
    text-align: center;
}

.retro-student-form .retro-input-label {
    text-align: left;
}

.retro-student-form .retro-input-shell .form-control {
    font-size: clamp(1rem, 1.8vw, 1.15rem);
    letter-spacing: clamp(0.24em, 0.45vw, 0.35em);
}

.retro-student-form .retro-action {
    width: 100%;
}

@media (max-width: 920px) {
    .retro-student-shell,
    .retro-student-panel,
    .retro-student-form {
        width: min(100%, 44rem);
    }
}

@media (max-width: 640px) {
    .retro-student-screen {
        min-height: var(--retro-stage-min-height-mobile);
        gap: 0.85rem;
    }

    .retro-student-shell,
    .retro-student-panel,
    .retro-student-form {
        width: 100%;
    }

    .retro-student-panel {
        padding: 0.85rem;
    }

    .retro-student-panel .retro-note {
        text-align: left;
        font-size: 0.96rem;
    }

    .retro-student-form .retro-input-label {
        font-size: 0.84rem;
        letter-spacing: 0.14em;
    }

    .retro-student-form .retro-input-shell .form-control {
        min-height: 50px;
        font-size: 0.96rem;
        letter-spacing: 0.2em;
    }

    .retro-student-form .retro-input-shell .btn {
        min-width: 54px;
    }
}

@media (max-width: 420px) {
    .retro-student-panel .retro-note {
        font-size: 0.9rem;
    }

    .retro-student-form .retro-input-shell .form-control {
        letter-spacing: 0.12em;
    }
}
</style>

<div class="retro-page">
    <div class="retro-frame">
        <div class="retro-monitor fade-in" data-focus-on-load="student_code" style="animation-delay: 0.1s;">
            <div class="retro-screen">
                <div class="retro-screen__inner retro-student-screen">
                    <div class="retro-screen-bar retro-screen-bar--stop">
                        <span class="retro-screen-bar__label retro-screen-bar__label--stop">
                            <span class="retro-screen-bar__stop-icon" aria-hidden="true"></span>
                            <span>STOP</span>
                        </span>
                        <span class="retro-screen-meta">access code</span>
                    </div>

                    <div class="retro-student-shell">
                        <div class="retro-screen-heading">
                            <h1 class="retro-screen-heading__title">{{ $quiz->title }}</h1>
                            <p class="retro-screen-heading__subtitle">Retro AXD 3.0</p>
                        </div>

                        @if($quiz->description)
                            <div class="retro-panel retro-student-panel">
                                <p class="retro-note mb-0">{{ $quiz->description }}</p>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="retro-panel retro-student-panel">
                                <p class="retro-note mb-0">{{ session('error') }}</p>
                            </div>
                        @endif

                        <form action="{{ route('quiz.validate_student') }}" method="POST" class="retro-form-stack retro-student-form">
                            @csrf

                            <div>
                                <label for="student_code" class="retro-input-label">{{ __('join.student_code_label') }}</label>
                                <div class="input-group retro-input-shell">
                                    <input type="password"
                                           name="student_code"
                                           id="student_code"
                                           required
                                           maxlength="4"
                                           class="form-control text-center"
                                           placeholder="{{ __('join.placeholder_code') }}">
                                    <button class="btn" type="button" data-password-toggle data-password-toggle-target="student_code">
                                        <i class="fas fa-eye" data-password-toggle-icon></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="retro-action retro-action--primary">
                                {{ __('join.start_quiz') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
