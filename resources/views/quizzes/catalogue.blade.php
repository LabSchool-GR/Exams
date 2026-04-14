@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-globe"></i>
                        {{ __('catalogue.shared_quizzes') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('catalogue.available_quizzes') }}</h1>
                    <p class="dashboard-page-header__text mw-100">{{ __('catalogue.catalogue_intro') }}</p>
                </div>
            </div>

            <form method="GET" class="dashboard-form-panel mb-4">
                <div class="dashboard-form-grid">
                    <div class="dashboard-form-field">
                        <select name="category_id" id="category_id" class="dashboard-select dashboard-select--compact" aria-label="{{ __('catalogue.filter_by_category') }}">
                            <option value="">{{ __('catalogue.all_categories') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string) $categoryId === (string) $cat->id)>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-filter me-2"></i>{{ __('catalogue.apply_filter') }}
                        </button>
                    </div>
                </div>
            </form>

            @if ($quizzes->isEmpty())
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h2 class="dashboard-empty-state__title">{{ __('catalogue.no_quizzes_found') }}</h2>
                    <p class="dashboard-empty-state__text">{{ __('catalogue.catalogue_empty_text') }}</p>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @foreach ($quizzes as $quiz)
                        <article class="dashboard-collection-card dashboard-collection-card--stacked-actions">
                            <div class="dashboard-collection-card__body">
                                <div class="dashboard-collection-card__main">
                                    <div class="dashboard-collection-card__icon">
                                        <i class="fas fa-file-signature"></i>
                                    </div>

                                    <div>
                                        <h2 class="dashboard-collection-card__title">{{ $quiz->title }}</h2>
                                        <p class="dashboard-collection-card__text">
                                            {{ $quiz->description ?: __('catalogue.no_description') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="dashboard-collection-meta">
                                    <span class="dashboard-collection-pill">
                                        <i class="fas fa-user"></i>{{ $quiz->creator->name ?? __('catalogue.unknown_creator') }}
                                    </span>

                                    @if($quiz->category)
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-folder-open"></i>{{ $quiz->category->name }}
                                        </span>
                                    @endif

                                    <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                        <i class="fas fa-calendar-alt"></i>{{ $quiz->created_at->format('d/m/Y') }}
                                    </span>

                                    <a
                                        href="{{ $quiz->publicAccessUrl() }}"
                                        class="dashboard-primary-button dashboard-secondary-button--compact dashboard-catalogue-join-button ms-lg-auto"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i class="fas fa-right-to-bracket me-2"></i>{{ __('catalogue.join_quiz') }}
                                    </a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    {{ $quizzes->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
