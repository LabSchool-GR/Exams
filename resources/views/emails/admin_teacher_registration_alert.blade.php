@extends('emails.layout')

@section('email_title', __('emails.admin_teacher_registration.title'))
@section('email_intro', __('emails.admin_teacher_registration.intro'))

@section('email_body')
    <p style="margin: 0 0 16px; color: #6b7280;">{{ __('emails.admin_teacher_registration.privacy_note') }}</p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px; margin-bottom: 20px;">
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb; width: 220px;"><strong>{{ __('emails.admin_teacher_registration.registered_at') }}</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $registeredAt }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 8px;"><strong>{{ __('emails.admin_teacher_registration.admin_review') }}</strong></p>
    <ul style="margin: 0 0 20px; padding-left: 18px;">
        <li><a href="{{ $usersUrl }}" target="_blank" rel="noopener noreferrer">{{ __('emails.admin_teacher_registration.open_users_management') }}</a></li>
    </ul>
@endsection

@section('email_footer_note', __('emails.admin_teacher_registration.footer_note'))
