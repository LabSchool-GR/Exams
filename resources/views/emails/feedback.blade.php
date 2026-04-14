@extends('emails.layout')

@section('email_title', __('emails.feedback_alert.title'))
@section('email_intro', __('emails.feedback_alert.intro'))

@section('email_body')
    <p style="margin: 0 0 16px; color: #6b7280;">{{ __('emails.feedback_alert.privacy_note') }}</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px; margin-bottom: 20px;">
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb; width: 220px;"><strong>{{ __('emails.feedback_alert.submitted_at') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $submittedAt }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>{{ __('emails.feedback_alert.title_label') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $titleText }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 8px;"><strong>{{ __('emails.feedback_alert.message_label') }}</strong></p>
    <div style="border: 1px solid #d1d5db; background: #f9fafb; padding: 12px; white-space: pre-wrap;">{{ $messageBody }}</div>
@endsection
