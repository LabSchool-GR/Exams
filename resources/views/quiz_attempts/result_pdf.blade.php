<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdfexp.title_page') }}</title>
    <style>
        @page {
            margin: 28px 26px 42px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #18273f;
            margin: 0;
        }

        .report-shell {
            background: #ffffff;
        }

        .header {
            border: 1px solid #d8e2ef;
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 16px;
            background: #fbfdff;
        }

        .eyebrow {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #617391;
            margin-bottom: 4px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #11284d;
            margin: 0 0 4px;
        }

        .subtitle {
            font-size: 10px;
            color: #5d6f8c;
            margin: 0;
        }

        .summary-table,
        .metric-table,
        .question-layout,
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 50%;
            vertical-align: top;
        }

        .summary-left {
            padding-right: 8px;
        }

        .summary-right {
            padding-left: 8px;
        }

        .panel {
            border: 1px solid #d9e4f1;
            border-radius: 12px;
            background: #f8fbff;
            padding: 14px;
        }

        .panel-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #33517b;
            margin: 0 0 10px;
        }

        .quiz-name {
            font-size: 16px;
            font-weight: bold;
            color: #10294f;
            margin: 0 0 6px;
        }

        .quiz-description {
            font-size: 10px;
            color: #576883;
            margin: 0 0 12px;
        }

        .meta-row td {
            padding: 5px 0;
            vertical-align: top;
            border-bottom: 1px solid #e5edf7;
        }

        .meta-row:last-child td {
            border-bottom: none;
        }

        .meta-label {
            width: 42%;
            color: #60708a;
            padding-right: 10px;
        }

        .meta-value {
            color: #11284d;
            font-weight: bold;
        }

        .metric-strip {
            margin: 16px 0 18px;
        }

        .metric-table td {
            width: 25%;
            padding-right: 8px;
            vertical-align: top;
        }

        .metric-table td:last-child {
            padding-right: 0;
        }

        .metric-box {
            border: 1px solid #d9e4f2;
            border-radius: 12px;
            background: #ffffff;
            padding: 12px 10px;
            text-align: center;
        }

        .metric-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #617391;
            margin-bottom: 5px;
        }

        .metric-value {
            font-size: 18px;
            font-weight: bold;
            color: #10294f;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #10294f;
            margin: 0 0 10px;
        }

        .question-card {
            border: 1px solid #d8e3f0;
            border-radius: 12px;
            background: #ffffff;
            padding: 14px;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .question-header {
            margin-bottom: 10px;
        }

        .question-number {
            display: inline-block;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #5f7395;
            background: #edf4fc;
            border-radius: 999px;
            padding: 4px 8px;
            margin-bottom: 8px;
        }

        .question-text {
            font-size: 12px;
            font-weight: bold;
            color: #11284d;
        }

        .question-layout td {
            vertical-align: top;
        }

        .question-left {
            width: 62%;
            padding-right: 10px;
        }

        .question-right {
            width: 38%;
            padding-left: 10px;
        }

        .detail-box {
            border: 1px solid #e0e8f3;
            border-radius: 10px;
            background: #f8fbff;
            padding: 10px 12px;
            min-height: 82px;
        }

        .detail-title {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #5f7395;
            margin-bottom: 7px;
        }

        .detail-box ul {
            margin: 0;
            padding-left: 18px;
        }

        .detail-box li {
            margin-bottom: 4px;
        }

        .empty-answer {
            color: #7b879b;
            font-style: italic;
        }

        .status-box {
            border-radius: 10px;
            padding: 10px 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .status-correct {
            background: #eaf7ee;
            border: 1px solid #b9dec1;
            color: #1f6a36;
        }

        .status-incorrect {
            background: #fff1f1;
            border: 1px solid #ecc3c3;
            color: #9d2d2d;
        }

        .correct-answer-list {
            font-size: 10px;
            color: #4d5e77;
        }

        .correct-answer-list ul {
            margin: 6px 0 0;
            padding-left: 18px;
        }

        .signature {
            margin-top: 18px;
            text-align: right;
            color: #4f6079;
        }

        .signature-line {
            margin: 10px 0 4px;
            font-size: 16px;
            color: #8aa0bd;
        }
    </style>
</head>
<body>
@php
    $resolvedScore = number_format($scorePercentage ?? ($attempt->score ?? 0), 2);
    $submissionDate = $attempt->submitted_at
        ? \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y H:i')
        : '—';
    $questionIds = array_keys($questionResults);
    $totalQuestions = count($questionIds);
    $correctCount = count(array_filter($questionResults));
    $wrongCount = max($totalQuestions - $correctCount, 0);
    $description = trim((string) ($quiz->description ?? ''));
    $passed = (float) ($scorePercentage ?? ($attempt->score ?? 0)) >= (float) ($quiz->pass_percentage ?? 0);
@endphp

<div class="report-shell">
    <div class="header">
        <div class="eyebrow">{{ __('pdfexp.title') }}</div>
        <div class="title">{{ $quiz->title }}</div>
        <p class="subtitle">{{ __('pdfexp.subtitle') }}</p>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-left">
                <div class="panel">
                    <div class="panel-title">{{ __('pdfexp.quiz_info') }}</div>
                    <div class="quiz-name">{{ $quiz->title }}</div>

                    @if ($description !== '')
                        <p class="quiz-description">{{ $description }}</p>
                    @endif

                    <table class="detail-table">
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.quiz_code') }}</td>
                            <td class="meta-value">{{ $quiz->quiz_code }}</td>
                        </tr>
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.quiz_teacher') }}</td>
                            <td class="meta-value">{{ $quiz->creator->name }}</td>
                        </tr>
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.submission_date') }}</td>
                            <td class="meta-value">{{ $submissionDate }}</td>
                        </tr>
                    </table>
                </div>
            </td>
            <td class="summary-right">
                <div class="panel">
                    <div class="panel-title">{{ __('pdfexp.student_info') }}</div>

                    <table class="detail-table">
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.student_name') }}</td>
                            <td class="meta-value">{{ $attempt->student_name ?? '—' }}</td>
                        </tr>
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.student_code') }}</td>
                            <td class="meta-value">{{ $attempt->student_code ?? '—' }}</td>
                        </tr>
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.attempt_id') }}</td>
                            <td class="meta-value">{{ $attempt->id }}</td>
                        </tr>
                        <tr class="meta-row">
                            <td class="meta-label">{{ __('pdfexp.evaluation') }}</td>
                            <td class="meta-value">{{ $passed ? __('pdfexp.correct') : __('pdfexp.incorrect') }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <div class="metric-strip">
        <table class="metric-table">
            <tr>
                <td>
                    <div class="metric-box">
                        <div class="metric-label">{{ __('pdfexp.score_percentage') }}</div>
                        <div class="metric-value">{{ $resolvedScore }}%</div>
                    </div>
                </td>
                <td>
                    <div class="metric-box">
                        <div class="metric-label">{{ __('pdfexp.question') }}</div>
                        <div class="metric-value">{{ $totalQuestions }}</div>
                    </div>
                </td>
                <td>
                    <div class="metric-box">
                        <div class="metric-label">{{ __('pdfexp.correct') }}</div>
                        <div class="metric-value">{{ $correctCount }}</div>
                    </div>
                </td>
                <td>
                    <div class="metric-box">
                        <div class="metric-label">{{ __('pdfexp.incorrect') }}</div>
                        <div class="metric-value">{{ $wrongCount }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section-title">{{ __('pdfexp.answer_summary') }}</div>

    @foreach($questionResults as $questionId => $isCorrect)
        @php
            $question = $quiz->questions->firstWhere('id', $questionId);
            $studentAnswers = $groupedAnswersByQuestion->get($questionId, collect());
        @endphp

        <div class="question-card">
            <div class="question-header">
                <div class="question-number">{{ __('pdfexp.question') }} {{ $loop->iteration }}</div>
                <div class="question-text">{{ $question->text ?? '—' }}</div>
            </div>

            <table class="question-layout">
                <tr>
                    <td class="question-left">
                        <div class="detail-box">
                            <div class="detail-title">{{ __('pdfexp.your_answer') }}</div>

                            @if($studentAnswers->isEmpty())
                                <div class="empty-answer">{{ __('pdfexp.not_answered') }}</div>
                            @else
                                <ul>
                                    @foreach($studentAnswers as $a)
                                        <li>{{ $a->answer->text ?? '-' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </td>
                    <td class="question-right">
                        <div class="status-box {{ $isCorrect ? 'status-correct' : 'status-incorrect' }}">
                            {{ $isCorrect ? __('pdfexp.correct') : __('pdfexp.incorrect') }}
                        </div>

                        @if(!$isCorrect && !empty($correctAnswersMap[$questionId]))
                            <div class="detail-box correct-answer-list">
                                <div class="detail-title">{{ __('pdfexp.correct_answers_label') }}</div>
                                <ul>
                                    @foreach($correctAnswersMap[$questionId] as $correctAnswer)
                                        <li>{{ $correctAnswer }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="signature">
        <div><strong>{{ __('pdfexp.teacher_signature') }}</strong></div>
        <div class="signature-line">__________________________</div>
        <div>{{ $quiz->creator->name }}</div>
    </div>
</div>
</body>
</html>
