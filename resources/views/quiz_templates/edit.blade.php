@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 64rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-pen-to-square"></i>
                        {{ __('templates.edit_title') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $quizTemplate->name }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('templates.edit_text') }}</p>
                </div>
                <a href="{{ route('quiz_templates.index') }}" class="dashboard-secondary-button">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('common.back') }}
                </a>
            </div>

            @if($errors->any())
                <div class="dashboard-status-card dashboard-status-card--danger">
                    <ul class="dashboard-status-list">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('quiz_templates.update', $quizTemplate) }}" class="dashboard-form-grid">
                @csrf
                @method('PUT')

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="code">{{ __('templates.code') }}</label>
                    <input id="code" type="text" name="code" value="{{ $quizTemplate->code }}" readonly class="dashboard-input">
                </div>

                <div class="dashboard-form-field">
                    <label class="dashboard-form-label" for="name">{{ __('templates.name') }}</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $quizTemplate->name) }}" required class="dashboard-input">
                </div>

                <div class="dashboard-form-field dashboard-form-field--full">
                    <label class="dashboard-form-label" for="description">{{ __('templates.description') }}</label>
                    <textarea id="description" name="description" rows="4" class="dashboard-textarea">{{ old('description', $quizTemplate->description) }}</textarea>
                </div>

                <div class="form-check dashboard-switch-card dashboard-form-field--full">
                    <input class="form-check-input" type="checkbox" name="is_common" id="is_common" value="1" @checked(old('is_common', $quizTemplate->is_common)) data-visibility-toggle data-visibility-toggle-target="users-select-wrapper">
                    <label class="form-check-label" for="is_common">{{ __('templates.is_common') }}</label>
                    <div class="form-text">{{ __('templates.common_template_help') }}</div>
                </div>

                <div id="users-select-wrapper" class="dashboard-form-field dashboard-form-field--full">
                    <label class="dashboard-form-label" for="users">{{ __('templates.assign_users') }}</label>
                    <p class="dashboard-page-header__text">{{ __('templates.assign_users_help') }}</p>
                    <select id="users" name="users[]" multiple class="dashboard-select dashboard-select--multiple">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(collect(old('users', $selectedUsers))->contains($user->id))>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="dashboard-form-actions">
                    <button type="submit" class="dashboard-primary-button">
                        <i class="fas fa-save me-2"></i>{{ __('templates.update') }}
                    </button>
                </div>
            </form>
        </section>
    </div>
</div>
@endsection