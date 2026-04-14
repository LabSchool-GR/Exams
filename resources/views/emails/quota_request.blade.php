@extends('emails.layout')

@section('email_title', __('emails.quota_request.title'))
@section('email_intro', __('emails.quota_request.intro', ['resource' => $payload['resource_label']]))

@section('email_body')
    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px; margin-bottom: 20px;">
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb; width: 220px;"><strong>{{ __('emails.quota_request.user') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['user_name'] }} ({{ $payload['user_email'] }})</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>{{ __('emails.quota_request.request_type') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['resource_label'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>{{ __('emails.quota_request.current_usage') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['current_usage'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>{{ __('emails.quota_request.current_limit') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['current_limit'] }}</td>
        </tr>
        @if(!empty($payload['quiz_title']))
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>{{ __('emails.quota_request.quiz') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['quiz_title'] }}</td>
        </tr>
        @endif
        @if(!empty($payload['question_text']))
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>{{ __('emails.quota_request.question') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['question_text'] }}</td>
        </tr>
        @endif
    </table>

    <p style="margin: 0 0 8px;"><strong>{{ __('emails.quota_request.quick_links') }}</strong></p>
    <ul style="margin: 0 0 20px; padding-left: 18px;">
        <li><a href="{{ $payload['users_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('emails.quota_request.open_users_management') }}</a></li>
        <li><a href="{{ $payload['user_profile_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('emails.quota_request.open_teacher_profile') }}</a></li>
        @if(!empty($payload['quiz_edit_url']))
        <li><a href="{{ $payload['quiz_edit_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('emails.quota_request.open_related_quiz') }}</a></li>
        @endif
        @if(!empty($payload['question_edit_url']))
        <li><a href="{{ $payload['question_edit_url'] }}" target="_blank" rel="noopener noreferrer">{{ __('emails.quota_request.open_related_question') }}</a></li>
        @endif
    </ul>
@endsection

@section('email_footer_note', __('emails.quota_request.footer_note'))
