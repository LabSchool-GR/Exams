<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('email_title')</title>
</head>
<body style="margin: 0; padding: 16px; background: #f3f6fb;">
    <div style="max-width: 760px; margin: 0 auto; font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px;">
        <h2 style="margin: 0 0 16px; color: #0f4c81;">@yield('email_title')</h2>

        @hasSection('email_intro')
            <p style="margin: 0 0 16px;">@yield('email_intro')</p>
        @endif

        <div>
            @yield('email_body')
        </div>

        @hasSection('email_footer_note')
            <p style="margin: 16px 0 0; color: #6b7280;">@yield('email_footer_note')</p>
        @endif
    </div>
</body>
</html>