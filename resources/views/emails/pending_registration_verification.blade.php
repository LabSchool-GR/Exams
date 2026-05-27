@extends('emails.layout')

@section('email_title', __('emails.pending_registration.title'))
@section('email_intro', __('emails.pending_registration.intro', ['name' => $name]))

@section('email_body')
    <p style="margin: 0 0 16px;">{{ __('emails.pending_registration.instructions') }}</p>

    <p style="margin: 0 0 20px;">
        <a href="{{ $verificationUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-block; padding: 10px 16px; color: #ffffff; background: #0f4c81; border-radius: 8px; text-decoration: none; font-weight: 700;">
            {{ __('emails.pending_registration.action') }}
        </a>
    </p>

    <p style="margin: 0 0 16px; color: #6b7280;">
        {{ __('emails.pending_registration.expires', ['expires_at' => $expiresAt]) }}
    </p>

    <p style="margin: 0;">
        <a href="{{ $verificationUrl }}" target="_blank" rel="noopener noreferrer">{{ $verificationUrl }}</a>
    </p>
@endsection

@section('email_footer_note', __('emails.pending_registration.notice'))
