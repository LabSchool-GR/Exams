<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ $quiz->title }} {{ __('pdfexp.print_title_suffix') }}</title>
    <style>
        @page {
            margin: 28px 26px 42px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #18273f;
            margin: 0;
            background: #ffffff;
        }

        .document-shell {
            width: 100%;
        }

        .hero-card,
        .candidate-card,
        .question-card,
        .signature-card {
            border: 1px solid #d9e4f1;
            border-radius: 14px;
            background: #ffffff;
        }

        .hero-card {
            padding: 18px 20px;
            margin-bottom: 16px;
            background: #fbfdff;
        }

        .eyebrow {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.1px;
            color: #617391;
            margin-bottom: 6px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #10294f;
            margin: 0 0 8px;
            line-height: 1.2;
        }

        .description {
            font-size: 10px;
            color: #5d6f8c;
            margin: 0;
        }

        .meta-table,
        .candidate-table {
            width: 100%;
            border-collapse: collapse;
        }

        .meta-table {
            margin-top: 14px;
        }

        .meta-table td {
            width: 33.33%;
            vertical-align: top;
            padding-right: 10px;
        }

        .meta-table td:last-child {
            padding-right: 0;
        }

        .meta-box {
            border: 1px solid #e2eaf4;
            border-radius: 10px;
            background: #ffffff;
            padding: 10px 12px;
            min-height: 52px;
        }

        .meta-label {
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #6a7c97;
            margin-bottom: 5px;
        }

        .meta-value {
            font-size: 11px;
            font-weight: bold;
            color: #11284d;
        }

        .candidate-card {
            padding: 12px 14px;
            margin-bottom: 18px;
            background: #f9fbfe;
        }

        .candidate-table td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }

        .candidate-table td:last-child {
            padding-right: 0;
        }

        .candidate-label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7d96;
            margin-bottom: 8px;
        }

        .candidate-line {
            border-bottom: 1px solid #9fb2c9;
            height: 20px;
        }

        .question-card {
            padding: 14px 15px;
            margin-bottom: 14px;
            page-break-inside: avoid;
            background: #ffffff;
        }

        .question-number {
            display: inline-block;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #47658f;
            background: #edf4fc;
            border-radius: 999px;
            padding: 4px 8px;
            margin-bottom: 8px;
        }

        .question-text {
            font-size: 12px;
            font-weight: bold;
            color: #10294f;
            margin: 0 0 8px;
            line-height: 1.4;
        }

        .image-note {
            font-size: 9px;
            color: #7b6a52;
            background: #fff7ed;
            border: 1px solid #f1dcc2;
            border-radius: 8px;
            padding: 6px 8px;
            margin: 0 0 10px;
        }

        .answers {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .answers li {
            border: 1px solid #e0e8f2;
            border-radius: 10px;
            background: #fbfdff;
            padding: 8px 10px 8px 34px;
            margin-bottom: 7px;
            position: relative;
            color: #213547;
        }

        .answers li:last-child {
            margin-bottom: 0;
        }

        .answers li .answer-badge {
            position: absolute;
            left: 10px;
            top: 8px;
            width: 16px;
            color: #617391;
            font-weight: bold;
        }

        .signature-card {
            margin-top: 20px;
            padding: 14px 16px;
            background: #fbfdff;
        }

        .signature-title {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.9px;
            color: #617391;
            margin-bottom: 20px;
        }

        .signature-line {
            width: 180px;
            border-bottom: 1px solid #8ea4bf;
            margin: 0 0 8px auto;
        }

        .signature-name {
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            color: #11284d;
        }
    </style>
</head>
<body>
@php
    $questionCount = $questions->count();
@endphp

<div class="document-shell">
    <div class="hero-card">
        <div class="eyebrow">{{ __('pdfexp.print_title_suffix') }}</div>
        <h1 class="title">{{ $quiz->title }}</h1>

        @if($quiz->description)
            <p class="description">{!! nl2br(e($quiz->description)) !!}</p>
        @endif

        <table class="meta-table">
            <tr>
                <td>
                    <div class="meta-box">
                        <div class="meta-label">{{ __('pdfexp.quiz_code') }}</div>
                        <div class="meta-value">{{ $quiz->quiz_code }}</div>
                    </div>
                </td>
                <td>
                    <div class="meta-box">
                        <div class="meta-label">{{ __('pdfexp.quiz_teacher') }}</div>
                        <div class="meta-value">{{ $quiz->creator->name }}</div>
                    </div>
                </td>
                <td>
                    <div class="meta-box">
                        <div class="meta-label">{{ __('pdfexp.quiz_timer') }}</div>
                        <div class="meta-value">{{ $quiz->has_timer ? __('pdfexp.yes') : __('pdfexp.no') }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="candidate-card">
        <table class="candidate-table">
            <tr>
                <td>
                    <span class="candidate-label">{{ __('pdfexp.student_name_label') }}</span>
                    <div class="candidate-line"></div>
                </td>
                <td>
                    <span class="candidate-label">{{ __('pdfexp.registry_number_label') }}</span>
                    <div class="candidate-line"></div>
                </td>
            </tr>
        </table>
    </div>

    @foreach ($questions as $index => $question)
        <div class="question-card">
            <div class="question-number">{{ __('pdfexp.question_label') }} {{ $index + 1 }} / {{ $questionCount }}</div>
            <p class="question-text">{{ $question->text }}</p>

            @if ($question->image)
                <div class="image-note">{{ __('pdfexp.image_note') }}</div>
            @endif

            <ul class="answers">
                @foreach ($question->answers as $answer)
                    <li>
                        <span class="answer-badge">{{ chr(64 + $loop->iteration) }}.</span>
                        <span>{{ $answer->text }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <div class="signature-card">
        <div class="signature-title">{{ __('pdfexp.teacher_signature') }}</div>
        <div class="signature-line"></div>
        <div class="signature-name">{{ $quiz->creator->name }}</div>
    </div>
</div>
</body>
</html>
