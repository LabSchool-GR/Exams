<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('quizzes.anonymous_cards_pdf_title') }}</title>
    <style>
        @page { margin: 18mm 12mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1 { margin: 0 0 6px; font-size: 18px; }
        p { margin: 0 0 14px; color: #4b5563; }
        table { width: 100%; border-collapse: collapse; }
        td { width: 50%; padding: 8px; vertical-align: top; }
        .card { border: 1px solid #d1d5db; border-radius: 10px; padding: 14px; min-height: 250px; }
        .label { font-size: 11px; color: #6b7280; text-transform: uppercase; }
        .value { font-size: 18px; font-weight: bold; margin: 4px 0 10px; }
        .muted { font-size: 11px; color: #6b7280; margin-bottom: 10px; }
        .qr { text-align: center; margin: 10px 0; }
        .url { font-size: 9px; color: #4b5563; word-break: break-all; }
    </style>
</head>
<body>
    <h1>{{ __('quizzes.anonymous_cards_pdf_title') }}</h1>
    <p>{{ __('quizzes.anonymous_cards_pdf_hint', ['quiz' => $quiz->title]) }}</p>

    <table>
        <tbody>
        @foreach ($cards->chunk(2) as $row)
            <tr>
                @foreach ($row as $card)
                    <td>
                        <div class="card">
                            <div class="label">{{ __('quizzes.student_name') }}</div>
                            <div class="value">{{ $card['student_name'] }}</div>

                            <div class="label">{{ __('quizzes.student_code') }}</div>
                            <div class="value">{{ $card['student_code'] }}</div>

                            <div class="muted">{{ __('quizzes.max_attempts') }}: {{ $card['max_attempts'] }}</div>

                            @if (!empty($card['qr_svg']))
                                <div class="qr">
                                    <img src="data:image/svg+xml;base64,{{ $card['qr_svg'] }}" alt="QR" width="130" height="130">
                                </div>
                            @endif

                            <div class="label">{{ __('quizzes.personal_link') }}</div>
                            <div class="url">{{ $card['student_url'] }}</div>
                        </div>
                    </td>
                @endforeach
                @if ($row->count() === 1)
                    <td></td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
