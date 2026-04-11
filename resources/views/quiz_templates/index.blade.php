@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 72rem; margin-inline: auto;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-object-group"></i>
                        {{ __('templates.index_title') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('templates.index_subtitle') }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('templates.manage_templates') }}</p>
                </div>
                <a href="{{ route('quiz_templates.create') }}" class="dashboard-primary-button">
                    <i class="fas fa-plus me-2"></i>{{ __('templates.create_title') }}
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

            @if($quizTemplates->isEmpty())
                <div class="dashboard-empty-state">
                    <span class="dashboard-empty-state__icon">
                        <i class="fas fa-layer-group"></i>
                    </span>
                    <h2 class="dashboard-empty-state__title">{{ __('templates.no_templates') }}</h2>
                    <p class="dashboard-empty-state__text">{{ __('templates.no_templates_text') }}</p>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach($quizTemplates as $quizTemplate)
                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__body">
                                <div class="dashboard-collection-card__heading">
                                    <div>
                                        <h2 class="dashboard-collection-card__title">{{ $quizTemplate->name }}</h2>
                                        <p class="dashboard-collection-card__meta">{{ $quizTemplate->description ?: __('templates.no_description') }}</p>
                                    </div>
                                </div>

                                <div class="dashboard-pill-row">
                                    <span class="dashboard-pill">
                                        <i class="fas fa-code"></i>
                                        {{ __('templates.code') }}: {{ $quizTemplate->code }}
                                    </span>
                                    <span class="dashboard-pill">
                                        {{ $quizTemplate->is_common ? __('templates.common_template') : __('templates.assigned_template') }}
                                    </span>
                                    <span class="dashboard-pill">
                                        <i class="fas fa-users"></i>
                                        {{ trans_choice('templates.assigned_users_count', $quizTemplate->users_count, ['count' => $quizTemplate->users_count]) }}
                                    </span>
                                    @unless($quizTemplate->is_common)
                                        @foreach($quizTemplate->users as $user)
                                            <span class="dashboard-pill">{{ $user->name }}</span>
                                        @endforeach
                                    @endunless
                                </div>
                            </div>

                            <div class="dashboard-collection-card__actions dashboard-collection-card__actions--compact">
                                <a href="{{ route('quiz_templates.edit', $quizTemplate) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-pen-to-square me-2"></i>{{ __('common.edit') }}
                                </a>
                                <form method="POST" action="{{ route('quiz_templates.destroy', $quizTemplate) }}" class="dashboard-inline-form" data-confirm-submit="{{ __('templates.confirm_delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dashboard-danger-button dashboard-danger-button--compact">
                                        <i class="fas fa-trash-alt me-2"></i>{{ __('common.delete') }}
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
