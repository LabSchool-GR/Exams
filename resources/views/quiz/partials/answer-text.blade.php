@php
    $showPrefix = $showPrefix ?? true;
    $answerPrefix = $showPrefix ? $quiz->answerLabelForIndex($answerIndex) : null;
@endphp

@if($answerPrefix)
    <span class="fw-semibold me-2">{{ $answerPrefix }}</span>
@endif

<span>{{ $answer->text }}</span>
