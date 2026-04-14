@extends('emails.layout')

@section('email_title', __('emails.quiz_success.title'))
@section('email_intro', __('emails.quiz_success.greeting'))

@section('email_body')
    <p style="margin: 0 0 12px;">{{ __('emails.quiz_success.lead', ['name' => $studentName]) }}</p>

    <ul style="margin: 0 0 16px; padding-left: 18px;">
        <li><strong>{{ __('emails.quiz_success.quiz_title_label') }}:</strong> {{ $quizTitle }}</li>
        <li><strong>{{ __('emails.quiz_success.score_label') }}:</strong> {{ $score }}%</li>
    </ul>

    <p style="margin: 0 0 12px;">{{ __('emails.quiz_success.more_info') }}</p>

    <p style="margin: 0 0 12px;">
        <a href="{{ route('dashboard') }}" target="_blank" rel="noopener noreferrer">{{ __('emails.quiz_success.open_dashboard') }}</a>
    </p>

    <p style="margin: 0;">{{ __('emails.quiz_success.signature') }}</p>
@endsection
