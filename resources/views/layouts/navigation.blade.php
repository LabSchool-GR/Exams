<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', __('navigation.app_name')) }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Shared icon font and typography assets --}}
    <link href="{{ asset('vendor/fontawesome/css/all.min.css') }}" rel="stylesheet">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    {{-- Shared asset bundle --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="app-shell">
    {{-- Primary staff navigation --}}
    <nav class="navbar app-navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('dashboard') }}">
                <i class="fas fa-graduation-cap text-primary me-2"></i> {{ __('navigation.app_name') }}
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain"
                    aria-controls="navMain" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMain">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quizzes.index') ? 'active text-primary fw-semibold' : '' }}"
                               href="{{ route('quizzes.index') }}">
                                <i class="fas fa-list-alt me-1"></i> {{ __('navigation.my_quizzes') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('quizzes.catalogue') ? 'active text-primary fw-semibold' : '' }}"
                               href="{{ route('quizzes.catalogue') }}">
                                <i class="fas fa-globe me-1"></i> {{ __('navigation.catalogue') }}
                            </a>
                        </li>
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                        <i class="fas fa-user-edit me-1"></i> {{ __('navigation.edit_profile') }}
                                    </a>
                                </li>
                                @if(Auth::user()->role === 'admin')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('quizzes.index') }}">
                                            <i class="fas fa-th-list me-1"></i> {{ __('navigation.all_quizzes') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('categories.index') }}">
                                            <i class="fas fa-layer-group me-1"></i> {{ __('navigation.categories') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('users.index') }}">
                                            <i class="fas fa-users-cog me-1"></i> {{ __('navigation.user_management') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('quiz_templates.index') }}">
                                            <i class="fas fa-users-cog me-1"></i> {{ __('navigation.templates') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('system_updates.index') }}">
                                            <i class="fas fa-cloud-arrow-down me-1"></i> {{ __('navigation.system_updates') }}
                                        </a>
                                    </li>
                                @endif
                                <li>
                                    <a class="dropdown-item" href="{{ route('about') }}">
                                        <i class="fas fa-info-circle me-1"></i> {{ __('navigation.info') }}
                                    </a>
                                </li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-1"></i> {{ __('navigation.logout') }}
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

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
    <main class="app-main py-4">
        @yield('content')
    </main>

    @include('layouts.partials.app_footer')
</body>
</html>
