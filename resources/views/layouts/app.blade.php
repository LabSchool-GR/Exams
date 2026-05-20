<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('layouts.partials.meta_tags')
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

<body class="bg-light text-dark app-body-flex">
    {{-- Shared flash toasts for authenticated pages --}}
    <div class="toast-container position-fixed top-0 end-0 p-3 app-toast-container">
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

    {{-- Main page content --}}
    <main class="flex-grow-1">
        @yield('content')
    </main>

    {{-- Keep the footer limited to authenticated staff-facing areas --}}
    @auth
        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
            @include('layouts.partials.app_footer')
        @endif
    @endauth
</body>
</html>
