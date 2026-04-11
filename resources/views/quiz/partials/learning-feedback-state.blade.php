@php
    $hasMultipleCorrectAnswers = count($learningCorrectAnswerIds ?? []) !== 1;
    $feedbackMessageKey = ($learningAnswerWasCorrect ?? false)
        ? ($hasMultipleCorrectAnswers ? 'join.learning_mode_feedback_correct_plural' : 'join.learning_mode_feedback_correct')
        : ($hasMultipleCorrectAnswers ? 'join.learning_mode_feedback_wrong_plural' : 'join.learning_mode_feedback_wrong');
    $feedbackButtonLabel = $isLastQuestion
        ? __('join.learning_mode_finish')
        : __('join.learning_mode_continue');
    $feedbackButtonClass = $isLastQuestion ? 'btn-success' : 'btn-primary';
@endphp

<div class="alert {{ ($learningAnswerWasCorrect ?? false) ? 'alert-success' : 'alert-warning' }} mt-4" role="status">
    <i class="fas {{ ($learningAnswerWasCorrect ?? false) ? 'fa-check-circle' : 'fa-circle-info' }} me-2"></i>
    {{ __($feedbackMessageKey) }}
</div>

<div class="list-group mt-4">
    @foreach($question->answers as $answerIndex => $answer)
        @php
            $answerId = (int) $answer->id;
            $isSelectedAnswer = in_array($answerId, $learningSelectedAnswerIds ?? [], true);
            $isCorrectAnswer = in_array($answerId, $learningCorrectAnswerIds ?? [], true);
            $itemClass = $isCorrectAnswer
                ? 'list-group-item-success'
                : ($isSelectedAnswer ? 'list-group-item-danger' : '');
            $iconClass = $isCorrectAnswer
                ? 'fa-check-circle text-success'
                : ($isSelectedAnswer ? 'fa-times-circle text-danger' : 'fa-circle text-muted');
        @endphp
        <div class="list-group-item d-flex align-items-center {{ $itemClass }}">
            <i class="fas {{ $iconClass }} me-2"></i>
            @include('quiz.partials.answer-text', ['answerIndex' => $answerIndex, 'answer' => $answer, 'quiz' => $quiz])
        </div>
    @endforeach
</div>

@if($isLastQuestion)
    <form action="{{ route('quiz.submit_final', ['quizKey' => $quizRouteKey]) }}" method="POST" class="mt-4">
        @csrf
        <button type="submit" class="btn {{ $feedbackButtonClass }} w-100">
            <i class="fas fa-flag-checkered me-1"></i> {{ $feedbackButtonLabel }}
        </button>
    </form>
@else
    <a href="{{ route('quiz.next_question', ['quizKey' => $quizRouteKey, 'currentQuestionKey' => $questionRouteKey]) }}" class="btn {{ $feedbackButtonClass }} w-100 mt-4">
        <i class="fas fa-arrow-right me-1"></i> {{ $feedbackButtonLabel }}
    </a>
@endif
