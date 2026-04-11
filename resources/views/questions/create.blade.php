@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 72rem; margin-inline: auto;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-plus-circle"></i>
                        {{ __('quizzes_cards.add_question') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $quiz->title }}</h1>
                </div>

                <a href="{{ route('quizzes.questions.index', $quiz) }}" class="btn dashboard-btn dashboard-btn--ghost">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes_cards.cancel') }}
                </a>
            </div>

            @include('questions._form', [
                'quiz' => $quiz,
                'formAction' => route('quizzes.questions.store', $quiz),
                'submitLabel' => __('quizzes_cards.save'),
            ])
        </section>
    </div>
</div>
@endsection
