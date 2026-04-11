<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdfexp.info_title') }}</title>
    <style>
        @page {
            margin: 26px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #14213d;
            margin: 0;
        }

        .sheet {
            border: 1px solid #d6deeb;
            border-radius: 14px;
            padding: 18px 20px 16px;
            background: #ffffff;
        }

        .header {
            border-bottom: 2px solid #dfe7f2;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .eyebrow {
            font-size: 9px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            color: #5c6f91;
            margin-bottom: 4px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0f274d;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 10px;
            color: #5f6f89;
            margin: 0;
        }

        .summary-table,
        .access-table,
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 50%;
            vertical-align: top;
        }

        .summary-cell-left {
            padding-right: 8px;
        }

        .summary-cell-right {
            padding-left: 8px;
        }

        .card {
            border: 1px solid #d9e3f0;
            background: #f8fbff;
            border-radius: 12px;
            padding: 14px;
        }

        .card-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #31507f;
            margin: 0 0 10px;
        }

        .quiz-name {
            font-size: 16px;
            font-weight: bold;
            color: #10294f;
            margin: 0 0 8px;
        }

        .quiz-description {
            font-size: 10px;
            color: #52627a;
            margin: 0 0 12px;
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            margin: 0 6px 6px 0;
            border-radius: 999px;
            background: #e7f0fb;
            color: #1f4b82;
            font-size: 9px;
            font-weight: bold;
        }

        .meta-row td {
            padding: 5px 0;
            vertical-align: top;
            border-bottom: 1px solid #e6edf7;
        }

        .meta-row:last-child td {
            border-bottom: none;
        }

        .meta-label {
            width: 42%;
            color: #5d6b82;
            padding-right: 10px;
        }

        .meta-value {
            color: #10294f;
            font-weight: bold;
        }

        .access-section {
            margin-top: 16px;
            border: 1px solid #d6deeb;
            border-radius: 12px;
            background: #fefefe;
            padding: 16px;
            page-break-inside: avoid;
        }

        .access-title {
            font-size: 14px;
            font-weight: bold;
            color: #0f274d;
            margin: 0 0 4px;
        }

        .access-subtitle {
            font-size: 10px;
            color: #62728c;
            margin: 0 0 12px;
        }

        .access-table td {
            vertical-align: top;
        }

        .access-main {
            width: 66%;
            padding-right: 12px;
        }

        .access-qr {
            width: 34%;
            text-align: center;
        }

        .action-button {
            display: inline-block;
            background: #2a6fd6;
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            font-size: 10px;
            padding: 9px 14px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .link-box,
        .pin-box {
            border: 1px solid #d9e4f2;
            border-radius: 10px;
            background: #f8fbff;
            padding: 10px 12px;
            margin-top: 10px;
        }

        .box-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #5b7095;
            margin-bottom: 6px;
        }

        .link-value {
            font-size: 8.7px;
            line-height: 1.35;
            color: #12345b;
            word-wrap: break-word;
            overflow-wrap: anywhere;
        }

        .link-value a {
            color: #12345b;
            text-decoration: none;
        }

        .pin-line {
            margin: 4px 0;
            color: #274164;
            font-size: 10px;
        }

        .pin-line strong {
            color: #0f274d;
        }

        .qr-frame {
            display: inline-block;
            border: 1px solid #d8e2ef;
            border-radius: 12px;
            background: #ffffff;
            padding: 12px;
        }

        .qr-caption {
            font-size: 9px;
            color: #5d6b82;
            margin-top: 8px;
        }

        .footer-note {
            margin-top: 14px;
            font-size: 9px;
            color: #70819c;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $resolvedQrSvg = $qrSvg ?? $qr_svg ?? null;
        $primaryUrl = $is_guest ? $guest_url : $student_url;
        $description = trim((string) ($quiz->description ?? ''));
        $accessPolicy = null;

        if (!$is_guest) {
            if ($show_personal_link && $show_pin_access) {
                $accessPolicy = __('pdfexp.access_policy_pin_and_links');
            } elseif ($show_personal_link) {
                $accessPolicy = __('pdfexp.access_policy_links_only');
            } else {
                $accessPolicy = __('pdfexp.access_policy_pin_only');
            }
        }
    @endphp

    <div class="sheet">
        <div class="header">
            <div class="eyebrow">{{ __('pdfexp.student_info') }}</div>
            <h1 class="title">{{ __('pdfexp.info_title') }}</h1>
            <p class="subtitle">{{ __('pdfexp.access_title') }} - {{ $quiz->title }}</p>
        </div>

        <table class="summary-table">
            <tr>
                <td class="summary-cell-left">
                    <div class="card">
                        <div class="card-title">{{ __('pdfexp.quiz_info') }}</div>
                        <div class="quiz-name">{{ $quiz->title }}</div>

                        @if ($description !== '')
                            <p class="quiz-description">{{ $description }}</p>
                        @endif

                        <div>
                            <span class="pill">{{ __('pdfexp.quiz_code') }}: {{ $quiz->quiz_code }}</span>
                            <span class="pill">{{ __('pdfexp.quiz_status') }}: {{ $quiz->status === 'active' ? __('pdfexp.status_active') : __('pdfexp.status_inactive') }}</span>
                            <span class="pill">{{ __('pdfexp.quiz_timer') }}: {{ $quiz->has_timer ? __('pdfexp.yes') : __('pdfexp.no') }}</span>
                            <span class="pill">{{ __('pdfexp.quiz_resume') }}: {{ $quiz->allow_resume ? __('pdfexp.yes') : __('pdfexp.no') }}</span>
                        </div>
                    </div>
                </td>
                <td class="summary-cell-right">
                    <div class="card">
                        <div class="card-title">{{ __('pdfexp.student_info') }}</div>

                        <table class="meta-table">
                            <tr class="meta-row">
                                <td class="meta-label">{{ __('pdfexp.student_name') }}</td>
                                <td class="meta-value">{{ $student->student_name }}</td>
                            </tr>
                            <tr class="meta-row">
                                <td class="meta-label">{{ __('pdfexp.student_code') }}</td>
                                <td class="meta-value">{{ $student->student_code }}</td>
                            </tr>
                            <tr class="meta-row">
                                <td class="meta-label">{{ __('pdfexp.max_attempts') }}</td>
                                <td class="meta-value">{{ $student->max_attempts }}</td>
                            </tr>
                            <tr class="meta-row">
                                <td class="meta-label">{{ __('pdfexp.quiz_teacher') }}</td>
                                <td class="meta-value">{{ $quiz->creator->name ?? '—' }}</td>
                            </tr>
                            @if ($accessPolicy)
                                <tr class="meta-row">
                                    <td class="meta-label">{{ __('pdfexp.access_policy') }}</td>
                                    <td class="meta-value">{{ $accessPolicy }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        @if (($is_guest && $guest_url) || (!$is_guest && ($student_url || $pin_join_url)))
            <div class="access-section">
                <div class="access-title">{{ __('pdfexp.access_title') }}</div>
                <div class="access-subtitle">{{ __('pdfexp.qr_label') }}</div>

                <table class="access-table">
                    <tr>
                        <td class="access-main">
                            @if ($primaryUrl)
                                <a href="{{ $primaryUrl }}" class="action-button">{{ __('pdfexp.start_quiz_button') }}</a>

                                <div class="link-box">
                                    <div class="box-label">
                                        {{ $is_guest ? __('pdfexp.or_visit') : __('pdfexp.personal_link_label') }}
                                    </div>
                                    <div class="link-value">
                                        <a href="{{ $primaryUrl }}">{{ $primaryUrl }}</a>
                                    </div>
                                </div>
                            @endif

                            @if (!$is_guest && $show_pin_access && $pin_join_url)
                                <div class="pin-box">
                                    <div class="box-label">{{ __('pdfexp.pin_access_label') }}</div>
                                    <div class="pin-line">
                                        <strong>{{ $join_url }}</strong>
                                    </div>
                                    <div class="pin-line">{{ __('pdfexp.insert_code') }} <strong>{{ $quiz->quiz_code }}</strong></div>
                                    <div class="pin-line">{{ __('pdfexp.insert_pin') }} <strong>{{ $student->student_code }}</strong></div>
                                </div>
                            @endif
                        </td>
                        <td class="access-qr">
                            @if ($resolvedQrSvg)
                                <div class="qr-frame">
                                    <img src="data:image/svg+xml;base64,{{ $resolvedQrSvg }}" width="132" alt="QR Code">
                                </div>
                                <div class="qr-caption">{{ __('pdfexp.qr_label') }}</div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="footer-note">{{ __('pdfexp.info_title') }} {{ __('pdfexp.print_title_suffix') }}</div>
    </div>
</body>
</html>
