<div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2 style="margin: 0 0 16px; color: #0f4c81;">Quota Increase Request</h2>

    <p style="margin: 0 0 16px;">
        A teacher requested an increase for <strong>{{ $payload['resource_label'] }}</strong>.
    </p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px; margin-bottom: 20px;">
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb; width: 180px;"><strong>User</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['user_name'] }} ({{ $payload['user_email'] }})</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>Request Type</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['resource_label'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>Current Usage</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['current_usage'] }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>Current Limit</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['current_limit'] }}</td>
        </tr>
        @if(!empty($payload['quiz_title']))
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>Quiz</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['quiz_title'] }}</td>
        </tr>
        @endif
        @if(!empty($payload['question_text']))
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>Question</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $payload['question_text'] }}</td>
        </tr>
        @endif
    </table>

    <p style="margin: 0 0 8px;"><strong>Quick links</strong></p>
    <ul style="margin: 0 0 20px; padding-left: 18px;">
        <li><a href="{{ $payload['users_url'] }}">Open users management</a></li>
        <li><a href="{{ $payload['user_profile_url'] }}">Open teacher profile</a></li>
        @if(!empty($payload['quiz_edit_url']))
        <li><a href="{{ $payload['quiz_edit_url'] }}">Open related quiz</a></li>
        @endif
        @if(!empty($payload['question_edit_url']))
        <li><a href="{{ $payload['question_edit_url'] }}">Open related question</a></li>
        @endif
    </ul>

    <p style="margin: 0; color: #6b7280;">
        The quota can be updated directly from the users management page.
    </p>
</div>
