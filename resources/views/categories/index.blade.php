@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--wide">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-folder-tree"></i>
                        {{ __('quizzes.category_description') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('quizzes.category_list') }}</h1>
                    <p class="dashboard-page-header__subtitle">{{ __('quizzes.category_help_text') }}</p>
                </div>
                <a href="{{ route('categories.create') }}" class="dashboard-primary-button">
                    <i class="fas fa-plus me-2"></i>{{ __('quizzes_cards.create_category') }}
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

            @if($categories->isEmpty())
                <div class="dashboard-empty-state">
                    <span class="dashboard-empty-state__icon">
                        <i class="fas fa-folder-open"></i>
                    </span>
                    <h2 class="dashboard-empty-state__title">{{ __('quizzes.no_categories') }}</h2>
                    <p class="dashboard-empty-state__text">{{ __('quizzes.category_empty_text') }}</p>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach($categories as $category)
                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__body">
                                <div class="dashboard-collection-card__heading">
                                    <div>
                                        <h2 class="dashboard-collection-card__title">{{ $category->name }}</h2>
                                        <p class="dashboard-collection-card__meta">{{ __('quizzes.category_card_text') }}</p>
                                    </div>
                                </div>

                                <div class="dashboard-pill-row">
                                    <span class="dashboard-pill">
                                        <i class="fas fa-layer-group"></i>
                                        {{ trans_choice('quizzes.category_quizzes_count', $category->quizzes_count, ['count' => $category->quizzes_count]) }}
                                    </span>
                                </div>
                            </div>

                            <div class="dashboard-collection-card__actions dashboard-collection-card__actions--compact">
                                <a href="{{ route('categories.edit', $category) }}" class="dashboard-secondary-button dashboard-secondary-button--compact">
                                    <i class="fas fa-pen-to-square me-2"></i>{{ __('common.edit') }}
                                </a>
                                <button
                                    type="button"
                                    class="dashboard-danger-button dashboard-danger-button--compact"
                                    data-submit-form="delete-category-{{ $category->id }}"
                                    data-confirm-message="{{ __('quizzes.confirm_delete_category') }}"
                                >
                                    <i class="fas fa-trash-alt me-2"></i>{{ __('common.delete') }}
                                </button>
                                <form id="delete-category-{{ $category->id }}" method="POST" action="{{ route('categories.destroy', $category) }}" class="dashboard-inline-form">
                                    @csrf
                                    @method('DELETE')
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