<div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2 style="margin: 0 0 16px; color: #0f4c81;">New feedback submission</h2>

    <p style="margin: 0 0 16px;">
        A feedback submission was sent through the application.
    </p>

    <p style="margin: 0 0 16px; color: #6b7280;">
        This notification excludes the sender account name and email address.
    </p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px; margin-bottom: 20px;">
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb; width: 180px;"><strong>Submitted at</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $submittedAt }}</td>
        </tr>
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb;"><strong>Title</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $titleText }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 8px;"><strong>Message</strong></p>
    <div style="border: 1px solid #d1d5db; background: #f9fafb; padding: 12px; white-space: pre-wrap;">{{ $messageBody }}</div>
</div>
