<div style="font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5;">
    <h2 style="margin: 0 0 16px; color: #0f4c81;">New teacher registration</h2>

    <p style="margin: 0 0 16px;">
        A new teacher account was registered in the application.
    </p>

    <p style="margin: 0 0 16px;">
        This notification intentionally excludes personal data.
    </p>

    <table cellpadding="8" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 720px; margin-bottom: 20px;">
        <tr>
            <td style="border: 1px solid #d1d5db; background: #f9fafb; width: 180px;"><strong>Registered at</strong></td>
            <td style="border: 1px solid #d1d5db;">{{ $registeredAt }}</td>
        </tr>
    </table>

    <p style="margin: 0 0 8px;"><strong>Admin review</strong></p>
    <ul style="margin: 0 0 20px; padding-left: 18px;">
        <li><a href="{{ $usersUrl }}">Open users management</a></li>
    </ul>

    <p style="margin: 0; color: #6b7280;">
        Review the admin panel for full account details.
    </p>
</div>
