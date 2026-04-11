@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--wide">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-users-gear"></i>
                        {{ __('quizzes.index_title_users') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('users.manage_users_subtitle') }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('users.manage_users') }}</p>
                </div>
                <a href="{{ route('users.create') }}" class="dashboard-primary-button">
                    <i class="fas fa-user-plus me-2"></i>{{ __('users.create_new_user') }}
                </a>
            </div>

            @if(session('success'))
                <div class="dashboard-status-card dashboard-status-card--success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="dashboard-status-card dashboard-status-card--danger">
                    {{ session('error') }}
                </div>
            @endif

            @if($users->isEmpty())
                <div class="dashboard-empty-state">
                    <span class="dashboard-empty-state__icon">
                        <i class="fas fa-users-slash"></i>
                    </span>
                    <h2 class="dashboard-empty-state__title">{{ __('users.no_users_found') }}</h2>
                    <p class="dashboard-empty-state__text">{{ __('users.no_users_text') }}</p>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach($users as $user)
                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__body">
                                <div class="dashboard-collection-card__heading">
                                    <div>
                                        <h2 class="dashboard-collection-card__title">{{ $user->name }}</h2>
                                        <p class="dashboard-collection-card__meta">{{ $user->email }}</p>
                                    </div>
                                </div>

                                <div class="dashboard-pill-row">
                                    <span class="dashboard-pill">
                                        <i class="fas fa-user-shield"></i>
                                        {{ __('users.role') }}: {{ __('users.' . $user->role) }}
                                    </span>
                                    <span class="dashboard-pill">{{ __('users.max_quizzes') }}: {{ $user->max_quizzes }}</span>
                                    <span class="dashboard-pill">{{ __('users.max_questions_per_quiz') }}: {{ $user->max_questions_per_quiz }}</span>
                                    <span class="dashboard-pill">{{ __('users.max_answers_per_question') }}: {{ $user->max_answers_per_question }}</span>
                                    <span class="dashboard-pill">{{ __('users.max_students_per_quiz') }}: {{ $user->max_students_per_quiz }}</span>
                                </div>
                            </div>

                            <div class="dashboard-collection-card__actions dashboard-collection-card__actions--compact">
                                <a href="{{ route('users.show', $user) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-eye me-2"></i>{{ __('users.view') }}
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-pen-to-square me-2"></i>{{ __('users.edit') }}
                                </a>

                                <form method="POST" action="{{ route('users.update', $user) }}" class="dashboard-inline-form">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="email" value="{{ $user->email }}">
                                    <input type="hidden" name="max_quizzes" value="{{ $user->max_quizzes }}">
                                    <input type="hidden" name="max_questions_per_quiz" value="{{ $user->max_questions_per_quiz }}">
                                    <input type="hidden" name="max_answers_per_question" value="{{ $user->max_answers_per_question }}">
                                    <input type="hidden" name="max_students_per_quiz" value="{{ $user->max_students_per_quiz }}">
                                    <select name="role" class="dashboard-select dashboard-select--compact" data-auto-submit aria-label="{{ __('users.role') }}">
                                        <option value="teacher" @selected($user->role === 'teacher')>{{ __('users.teacher') }}</option>
                                        <option value="admin" @selected($user->role === 'admin')>{{ __('users.admin') }}</option>
                                    </select>
                                </form>

                                <form method="POST" action="{{ route('users.destroy', $user) }}" class="dashboard-inline-form" data-confirm-submit="{{ __('users.delete_confirm') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dashboard-danger-button dashboard-danger-button--compact">
                                        <i class="fas fa-trash-alt me-2"></i>{{ __('users.delete') }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection