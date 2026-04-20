<!DOCTYPE html>
<html lang="{{ $quiz->language ?? app()->getLocale() }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel Quiz') }}</title>
    @yield('meta')

    {{-- Shared icon font assets --}}
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">

    {{-- Shared font loading --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Shared asset bundle --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-light text-dark quiz-participant-shell @yield('body_class')">
    {{-- Shared flash toasts for participant-facing pages --}}
    <div aria-live="polite" aria-atomic="true" class="position-fixed top-0 end-0 p-3 app-toast-container">
        @if(session('success'))
            <div class="toast align-items-center text-bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <main class="quiz-participant-main">
        @yield('content')
    </main>

    @php
        $isGuestParticipantFlow = (
            (string) session('student_code', '') === '0000'
            || (isset($attempt) && (string) ($attempt->student_code ?? '') === '0000')
        ) && !(bool) session('public_anonymous_pool_active', false);

        $participantNoticeKey = $isGuestParticipantFlow
            ? 'join.participant_notice_guest'
            : 'join.participant_notice';
    @endphp

    <div class="quiz-participant-meta">
        <aside class="quiz-disclaimer-panel" data-quiz-disclaimer aria-label="{{ __('join.participant_notice_aria') }}">
            <div class="quiz-disclaimer-panel__inner">
                <span class="quiz-disclaimer-panel__icon" aria-hidden="true">
                    <i class="fas fa-circle-info"></i>
                </span>
                <p class="quiz-disclaimer-panel__text">
                    {{ __($participantNoticeKey) }}
                </p>
                <button
                    type="button"
                    class="quiz-disclaimer-panel__dismiss"
                    data-quiz-disclaimer-dismiss
                    aria-label="{{ __('join.participant_notice_dismiss') }}"
                    title="{{ __('join.participant_notice_dismiss') }}"
                >
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </div>
        </aside>
    </div>
</body>

</html>
