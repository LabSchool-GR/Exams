<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdfexp.students_list_title') }}</title>
    <style>
        @page {
            margin: 26px;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #14213d;
        }

        .sheet {
            border: 1px solid #d6deeb;
            border-radius: 14px;
            padding: 18px 20px 16px;
            background: #ffffff;
        }

        .header {
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #dfe7f2;
        }

        .eyebrow {
            margin-bottom: 4px;
            color: #5c6f91;
            font-size: 9px;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .title {
            margin: 0 0 4px;
            color: #0f274d;
            font-size: 18px;
            font-weight: bold;
        }

        .subtitle {
            margin: 0;
            color: #5f6f89;
            font-size: 10px;
        }

        .summary-card {
            margin-bottom: 16px;
            padding: 13px 14px;
            border: 1px solid #d9e3f0;
            border-radius: 12px;
            background: #f8fbff;
        }

        .summary-title {
            margin: 0 0 8px;
            color: #31507f;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .quiz-name {
            margin: 0 0 8px;
            color: #10294f;
            font-size: 15px;
            font-weight: bold;
        }

        .pill {
            display: inline-block;
            margin: 0 6px 4px 0;
            padding: 4px 8px;
            border-radius: 999px;
            background: #e7f0fb;
            color: #1f4b82;
            font-size: 9px;
            font-weight: bold;
        }

        .section-title {
            margin: 0 0 9px;
            color: #0f274d;
            font-size: 13px;
            font-weight: bold;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .students-table thead {
            display: table-header-group;
        }

        .students-table tr {
            page-break-inside: avoid;
        }

        .students-table th {
            padding: 8px 7px;
            border: 1px solid #cbd8e8;
            background: #e7f0fb;
            color: #234a78;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.35px;
            text-transform: uppercase;
        }

        .students-table td {
            height: 28px;
            padding: 7px 8px;
            border: 1px solid #d9e3f0;
            background: #ffffff;
            color: #10294f;
            vertical-align: middle;
        }

        .students-table tbody tr:nth-child(even) td {
            background: #f8fbff;
        }

        .column-number {
            width: 6%;
            text-align: center;
        }

        .column-name {
            width: 46%;
            text-align: left;
        }

        .column-code {
            width: 26%;
            text-align: center;
        }

        .column-attempts {
            width: 22%;
            text-align: center;
        }

        .handwrite-line {
            display: block;
            width: 100%;
            min-height: 17px;
            border-bottom: 1.4px solid #31507f;
        }

        .footer-note {
            margin-top: 12px;
            color: #70819c;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="header">
            <div class="eyebrow">{{ __('pdfexp.student_list') }}</div>
            <h1 class="title">{{ __('pdfexp.students_list_title') }}</h1>
            <p class="subtitle">
                {{ __('pdfexp.issue_date') }} {{ now()->format('d/m/Y') }}
            </p>
        </div>

        <div class="summary-card">
            <div class="summary-title">{{ __('pdfexp.quiz_info') }}</div>
            <div class="quiz-name">{{ $quiz->title }}</div>
            <span class="pill">{{ __('pdfexp.quiz_code') }}: {{ $quiz->quiz_code }}</span>
            <span class="pill">{{ __('pdfexp.quiz_teacher') }}: {{ $quiz->creator->name ?? '—' }}</span>
            <span class="pill">{{ __('pdfexp.registered_students_count') }}: {{ count($data) }}</span>
        </div>

        <h2 class="section-title">{{ __('pdfexp.student_list') }}</h2>

        <table class="students-table">
            <thead>
                <tr>
                    <th class="column-number">#</th>
                    <th class="column-name">{{ __('pdfexp.student_name') }}</th>
                    <th class="column-code">{{ __('pdfexp.student_code') }}</th>
                    <th class="column-attempts">{{ __('pdfexp.max_attempts') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $index => $entry)
                    <tr>
                        <td class="column-number">{{ $index + 1 }}</td>
                        <td class="column-name">
                            @if($entry['is_anonymous'])
                                <span class="handwrite-line">&nbsp;</span>
                            @else
                                {{ $entry['name'] }}
                            @endif
                        </td>
                        <td class="column-code">{{ $entry['code'] }}</td>
                        <td class="column-attempts">{{ $entry['max_attempts'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="footer-note">{{ __('pdfexp.students_list_title') }} {{ __('pdfexp.print_title_suffix') }}</div>
    </div>
</body>
</html>
