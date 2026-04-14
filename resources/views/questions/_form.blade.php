@php
    use Illuminate\Support\Str;

    $isEdit = isset($question);
    $defaultAnswers = $isEdit
        ? $question->answers->map(fn ($answer) => [
            'id' => $answer->id,
            'text' => $answer->text,
            'is_correct' => (bool) $answer->is_correct,
        ])->all()
        : collect(range(1, 4))->map(fn () => ['id' => null, 'text' => '', 'is_correct' => false])->all();

    $answerRows = old('answers', $defaultAnswers);
    $maxAnswersPerQuestion = auth()->user()?->isAdmin() ? null : (int) (auth()->user()?->max_answers_per_question ?? 4);
    if (count($answerRows) < 2) {
        $answerRows[] = ['id' => null, 'text' => '', 'is_correct' => false];
        $answerRows[] = ['id' => null, 'text' => '', 'is_correct' => false];
    }
@endphp

@if($errors->any())
    <div class="dashboard-status-card dashboard-status-card--danger mb-4">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

@if($maxAnswersPerQuestion !== null)
    <div class="dashboard-status-card dashboard-status-card--warning mb-4">
        <i class="fas fa-circle-info"></i>
        <div>{{ __('controllers.answer_limit_editor_hint', ['limit' => $maxAnswersPerQuestion]) }}</div>
    </div>

    <form action="{{ route('quota_requests.store') }}" method="POST" class="mb-4">
        @csrf
        <input type="hidden" name="resource_type" value="answers">
        <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
        @isset($question)
            <input type="hidden" name="question_id" value="{{ $question->id }}">
        @endisset
        <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
            <i class="fas fa-envelope me-2"></i>{{ __('quizzes.request_more_answers_button') }}
        </button>
    </form>
@endif

<form action="{{ $formAction }}" method="POST" enctype="multipart/form-data" class="dashboard-form-stack">
    @csrf
    @isset($formMethod)
        @method($formMethod)
    @endisset

    <div class="dashboard-form-panel">
        <div class="dashboard-form-group">
            <label for="text" class="dashboard-form-label">
                <i class="fas fa-paragraph text-muted"></i>{{ __('quizzes_cards.question_text') }}
            </label>
            <input
                type="text"
                name="text"
                id="text"
                class="form-control dashboard-form-control @error('text') is-invalid @enderror"
                value="{{ old('text', $question->text ?? '') }}"
                required
                placeholder="{{ __('quizzes_cards.enter_question') }}"
            >
        </div>

        @if(Str::endsWith($quiz->question_view, '_img'))
            <div class="dashboard-form-group">
                <label for="image" class="dashboard-form-label">
                    <i class="fas fa-image text-muted"></i>{{ __('quizzes_cards.question_image') }}
                </label>
                <input type="file" name="image" id="image" class="form-control dashboard-form-control @error('image') is-invalid @enderror" accept="image/*" data-image-preview-target="image-preview">
                <img
                    id="image-preview"
                    src="{{ isset($question) && $question->image ? asset('storage/' . $question->image) : '#' }}"
                    data-preview-fallback-src="{{ isset($question) && $question->image ? asset('storage/' . $question->image) : '' }}"
                    class="mt-3 img-fluid dashboard-media-preview {{ isset($question) && $question->image ? '' : 'd-none' }}"
                    alt="Image Preview"
                >
                <div class="dashboard-form-help">{{ __('quizzes_cards.image_hint') }}</div>
            </div>

            @if(isset($question) && $question->image)
                <div class="form-check dashboard-switch-card">
                    <input class="form-check-input" type="checkbox" name="delete_image" id="delete_image" value="1">
                    <label class="form-check-label" for="delete_image">{{ __('quizzes_cards.delete_image') }}</label>
                </div>
            @endif
        @endif

        @if(!$quiz->is_random_order)
            <div class="dashboard-form-group">
                <label for="order" class="dashboard-form-label">
                    <i class="fas fa-sort-numeric-up text-muted"></i>{{ __('quizzes_cards.order') }}
                </label>
                <input
                    type="number"
                    name="order"
                    id="order"
                    class="form-control dashboard-form-control @error('order') is-invalid @enderror"
                    value="{{ old('order', $question->order ?? '') }}"
                    placeholder="{{ __('quizzes_cards.order_hint') }}"
                >
            </div>
        @endif
    </div>

    <div class="dashboard-form-panel">
        <div class="dashboard-page-header mb-0">
            <div>
                <span class="dashboard-section-card__eyebrow">
                    <i class="fas fa-list-check"></i>
                    {{ __('quizzes.manage_answers') }}
                </span>
                <p class="dashboard-page-header__text mb-0">{{ __('quizzes_cards.answers_editor_hint') }}</p>
            </div>

            <button
                type="button"
                class="btn dashboard-btn dashboard-btn--ghost"
                id="add-answer-row"
                @if($maxAnswersPerQuestion !== null && count($answerRows) >= $maxAnswersPerQuestion) disabled @endif
            >
                <i class="fas fa-plus me-2"></i>{{ __('quizzes_cards.add_answer_row') }}
            </button>
        </div>

        <div id="answers-editor" class="dashboard-answer-editor" data-answer-editor data-answer-template="answer-row-template" data-answer-add-button="add-answer-row" @if($maxAnswersPerQuestion !== null) data-max-answers="{{ $maxAnswersPerQuestion }}" @endif>
            @foreach($answerRows as $index => $answer)
                <div class="dashboard-answer-row" data-answer-row>
                    <input type="hidden" name="answers[{{ $index }}][id]" value="{{ $answer['id'] ?? '' }}">
                    <div class="row g-3 align-items-start">
                        <div class="col-md-8">
                            <label class="dashboard-form-label">{{ __('quizzes.answer_text') }}</label>
                            <input
                                type="text"
                                name="answers[{{ $index }}][text]"
                                class="form-control dashboard-form-control"
                                value="{{ $answer['text'] ?? '' }}"
                                placeholder="{{ __('quizzes.enter_answer') }}"
                            >
                        </div>
                        <div class="col-md-3">
                            <span class="dashboard-form-label dashboard-form-label--placeholder" aria-hidden="true">
                                {{ __('quizzes.answer_text') }}
                            </span>
                            <input type="hidden" name="answers[{{ $index }}][is_correct]" value="0">
                            <div class="form-check dashboard-switch-card">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="answers[{{ $index }}][is_correct]"
                                    value="1"
                                    id="answer_correct_{{ $index }}"
                                    {{ !empty($answer['is_correct']) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="answer_correct_{{ $index }}">
                                    {{ __('quizzes.is_correct') }}
                                </label>
                            </div>
                        </div>
                        <div class="col-md-1">
                            <span class="dashboard-form-label dashboard-form-label--placeholder" aria-hidden="true">
                                {{ __('quizzes.answer_text') }}
                            </span>
                            <div class="dashboard-answer-row__remove">
                                <button type="button" class="dashboard-answer-row__delete" data-remove-answer-row>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="dashboard-form-actions">
        <a href="{{ route('quizzes.questions.index', $quiz) }}" class="btn dashboard-btn dashboard-btn--ghost">
            <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes_cards.back') }}
        </a>

        <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
            <i class="fas fa-save me-2"></i>{{ $submitLabel }}
        </button>
    </div>
</form>

<template id="answer-row-template">
    <div class="dashboard-answer-row" data-answer-row>
        <input type="hidden" data-answer-id>
        <div class="row g-3 align-items-start">
            <div class="col-md-8">
                <label class="dashboard-form-label">{{ __('quizzes.answer_text') }}</label>
                <input type="text" class="form-control dashboard-form-control" data-answer-text placeholder="{{ __('quizzes.enter_answer') }}">
            </div>
            <div class="col-md-3">
                <span class="dashboard-form-label dashboard-form-label--placeholder" aria-hidden="true">
                    {{ __('quizzes.answer_text') }}
                </span>
                <input type="hidden" data-answer-correct-hidden value="0">
                <div class="form-check dashboard-switch-card">
                    <input class="form-check-input" type="checkbox" value="1" data-answer-correct>
                    <label class="form-check-label">{{ __('quizzes.is_correct') }}</label>
                </div>
            </div>
            <div class="col-md-1">
                <span class="dashboard-form-label dashboard-form-label--placeholder" aria-hidden="true">
                    {{ __('quizzes.answer_text') }}
                </span>
                <div class="dashboard-answer-row__remove">
                    <button type="button" class="dashboard-answer-row__delete" data-remove-answer-row>
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>