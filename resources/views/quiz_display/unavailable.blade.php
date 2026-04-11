@extends('layouts.quiz_guest')

@section('content')
<div class="quiz-display-shell quiz-display-shell--controller">
    <div class="quiz-display-controller">
        <div class="quiz-display-result">
            <div class="quiz-display-result__icon">
                <i class="fas fa-link-slash"></i>
            </div>
            <h1 class="quiz-display-result__title">{{ $title }}</h1>
            <p class="quiz-display-result__text">{{ $message }}</p>
        </div>
    </div>
</div>
@endsection
