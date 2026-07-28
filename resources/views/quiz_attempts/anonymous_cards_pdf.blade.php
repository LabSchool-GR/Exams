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
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        tr { page-break-inside: avoid; }
        .card-cell { width: 50%; padding: 6px; vertical-align: top; }
        .card { border: 1px solid #d1d5db; border-radius: 10px; padding: 14px; min-height: 250px; overflow: hidden; }
        .label { font-size: 11px; color: #6b7280; text-transform: uppercase; }
        .value { font-size: 18px; font-weight: bold; margin: 4px 0 10px; }
        .handwrite-line { display: block; min-height: 20px; margin: 5px 0 12px; border-bottom: 1.4px solid #374151; }
        .muted { font-size: 11px; color: #6b7280; margin-bottom: 10px; }
        .qr { text-align: center; margin: 10px 0; }
        .url { font-size: 8px; line-height: 1.35; color: #4b5563; overflow-wrap: break-word; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>{{ __('quizzes.anonymous_cards_pdf_title') }}</h1>
    <p>{{ __('quizzes.anonymous_cards_pdf_hint', ['quiz' => $quiz->title]) }}</p>

    <table>
        <colgroup>
            <col style="width: 50%;">
            <col style="width: 50%;">
        </colgroup>
        <tbody>
        @foreach ($cards->chunk(2) as $row)
            <tr>
                @foreach ($row as $card)
                    <td class="card-cell">
                        <div class="card">
                            <div class="label">{{ __('quizzes.student_name') }}</div>
                            <div class="handwrite-line">&nbsp;</div>

                            <div class="label">{{ __('quizzes.student_code') }}</div>
                            <div class="value">{{ $card['student_code'] }}</div>

                            <div class="muted">{{ __('quizzes.max_attempts') }}: {{ $card['max_attempts'] }}</div>

                            @if (!empty($card['qr_svg']))
                                <div class="qr">
                                    <img src="data:image/svg+xml;base64,{{ $card['qr_svg'] }}" alt="QR" width="130" height="130">
                                </div>
                            @endif

                            <div class="label">{{ __('quizzes.personal_link') }}</div>
                            <div class="url">@foreach(str_split((string) $card['student_url'], 18) as $urlChunk){{ $urlChunk }}&#8203;@endforeach</div>
                        </div>
                    </td>
                @endforeach
                @if ($row->count() === 1)
                    <td class="card-cell"></td>
                @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>
