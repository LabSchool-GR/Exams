<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

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

<body class="bg-light text-dark @yield('body_class')">
    {{-- Shared flash toasts for guest-facing pages --}}
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

    <main class="@yield('main_class')">
        @yield('content')
    </main>

    @hasSection('hide_guest_footer')
        @if (filled(config('app.source_url')))
            <div class="text-center small text-muted py-3">
                @include('layouts.partials.source_code_link')
            </div>
        @endif
    @else
        @include('layouts.partials.app_footer')
    @endif
</body>

</html>
