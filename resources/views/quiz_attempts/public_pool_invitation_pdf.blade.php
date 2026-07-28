<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('public_pool_invitation.document_title') }} — {{ $quiz->title }}</title>
    <style>
        @page { margin: 16mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #17304f;
            background: #ffffff;
        }
        .page {
            border: 1px solid #cfdceb;
            border-radius: 18px;
            padding: 26px 30px 22px;
            text-align: center;
        }
        .eyebrow {
            color: #18767d;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1.4px;
            text-transform: uppercase;
        }
        h1 {
            margin: 9px 0 4px;
            color: #102a4a;
            font-size: 25px;
        }
        .quiz-title {
            margin: 12px 0 4px;
            font-size: 21px;
            font-weight: bold;
            color: #183c66;
        }
        .description {
            margin: 5px auto 15px;
            max-width: 620px;
            color: #546982;
            font-size: 11px;
            line-height: 1.55;
        }
        .meta {
            width: 100%;
            margin: 12px 0 18px;
            border-collapse: separate;
            border-spacing: 7px 0;
        }
        .meta td {
            width: 33.33%;
            border: 1px solid #d8e3ef;
            border-radius: 10px;
            background: #f7fafc;
            padding: 9px 7px;
        }
        .meta-label {
            display: block;
            margin-bottom: 3px;
            color: #6a7c92;
            font-size: 8px;
            letter-spacing: .6px;
            text-transform: uppercase;
        }
        .meta-value {
            color: #173b62;
            font-size: 11px;
            font-weight: bold;
        }
        .qr-frame {
            display: inline-block;
            border: 1px solid #cfddeb;
            border-radius: 16px;
            background: #ffffff;
            padding: 14px;
        }
        .instruction {
            margin: 12px 0 7px;
            color: #173b62;
            font-size: 13px;
            font-weight: bold;
        }
        .url-box {
            margin: 8px auto 13px;
            max-width: 650px;
            border: 1px solid #d8e3ef;
            border-radius: 9px;
            background: #f7fafc;
            padding: 9px 12px;
            color: #355775;
            font-size: 8px;
            line-height: 1.4;
            overflow-wrap: break-word;
            word-wrap: break-word;
        }
        .notice {
            margin-top: 12px;
            border-top: 1px solid #e0e8f1;
            padding-top: 11px;
            color: #5c6f84;
            font-size: 9px;
            line-height: 1.5;
        }
        .expiration {
            margin-top: 7px;
            color: #7b4b28;
            font-size: 9px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="eyebrow">{{ __('public_pool_invitation.eyebrow') }}</div>
        <h1>{{ __('public_pool_invitation.document_title') }}</h1>
        <div class="quiz-title">{{ $quiz->title }}</div>

        @if (filled($quiz->description))
            <div class="description">{{ $quiz->description }}</div>
        @endif

        <table class="meta">
            <tr>
                <td>
                    <span class="meta-label">{{ __('public_pool_invitation.educator') }}</span>
                    <span class="meta-value">{{ $quiz->creator?->name ?? '—' }}</span>
                </td>
                <td>
                    <span class="meta-label">{{ __('public_pool_invitation.duration') }}</span>
                    <span class="meta-value">{{ __('public_pool_invitation.minutes', ['count' => max(1, (int) ceil($quiz->time_limit / 60))]) }}</span>
                </td>
                <td>
                    <span class="meta-label">{{ __('public_pool_invitation.capacity') }}</span>
                    <span class="meta-value">{{ $effectiveCapacity }}</span>
                </td>
            </tr>
        </table>

        <div class="qr-frame">
            <img src="data:image/svg+xml;base64,{{ $qrSvg }}" alt="{{ __('public_pool_invitation.qr_alt') }}" width="225" height="225">
        </div>

        <div class="instruction">{{ __('public_pool_invitation.instruction') }}</div>
        <div class="url-box">@foreach(str_split($publicUrl, 32) as $urlChunk){{ $urlChunk }}&#8203;@endforeach</div>

        <div class="notice">{{ __('public_pool_invitation.anonymous_notice') }}</div>

        @if ($expiresAt)
            <div class="expiration">
                {{ __('public_pool_invitation.expires_at', ['date' => $expiresAt->timezone(config('app.timezone'))->format('d/m/Y H:i')]) }}
            </div>
        @else
            <div class="expiration">{{ __('public_pool_invitation.no_expiration') }}</div>
        @endif
    </main>
</body>
</html>
