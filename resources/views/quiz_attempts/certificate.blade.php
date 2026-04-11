<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            text-align: center;
            border: 10px solid #ccc;
            padding: 20px 50px 10px 50px;  /* top, right, bottom, left */
        }

        h1 {
            font-size: 32px;
            margin-bottom: 30px;
        }

        .section {
            font-size: 20px;
            margin: 20px auto;
            width: 80%;
        }

        .footer {
            margin-top: 60px;
            font-size: 18px;
        }

        .signature {
            margin-top: 40px;
            font-weight: bold;
        }
    </style>
	@php
    use Illuminate\Support\Facades\URL;
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
    $showVerification = $quiz->usesCertificateVerification();
    $url = $showVerification
        ? URL::temporarySignedRoute(
            'quiz_attempts.verify',
            now()->addMinutes((int) config('security.signed_urls.certificate_verify_ttl_minutes', 43200)),
            ['attempt' => $attempt->id]
        )
        : null;
    $qrSvg = $showVerification && $url
        ? base64_encode(QrCode::format('svg')->size(100)->generate($url))
        : null;
	@endphp
</head>
<body>

    <h1>{{ __('pdfexp.certificate_title') }}</h1>

    <div class="section">
		{{ __('pdfexp.certificate_body_line1') }} <strong>{{ $attempt->student_name }}</strong><br>
		{{ __('pdfexp.certificate_body_line2') }}<br>
		{{ __('pdfexp.certificate_program') }}<br>
		<strong>"{{ $quiz->title }}"</strong><br>
		({{ __('pdfexp.certificate_code') }}: <strong>{{ $quiz->quiz_code }}</strong>)<br>
	</div>

    <div class="section">
		{{ __('pdfexp.certificate_date') }}: <strong>{{ $attempt->submitted_at->format('d/m/Y') }}</strong><br>
		{{ __('pdfexp.certificate_id') }}: <strong>#{{ $attempt->id }}</strong><br>
		{{ __('pdfexp.certificate_score') }}: <strong>{{ $attempt->score }}%</strong>
	</div>

	<div style="margin-top: 60px; width: 100%; display: table;">
        @if ($showVerification && $qrSvg)
		<div style="display: table-cell; width: 50%; padding-right: 20px; border-right: 1px solid #ccc; text-align: center;">
			<p style="font-size: 14px;">{{ __('pdfexp.certificate_verify_label') }}</p>
			<img src="data:image/svg+xml;base64,{{ $qrSvg }}" alt="QR Code">
			<p style="font-size: 12px;">{{ __('pdfexp.certificate_qr_note') }}</p>
            <p style="font-size: 10px; color: #555; margin-top: 12px;">{{ __('pdfexp.certificate_disclaimer') }}</p>
		</div>
        @endif

		<div style="display: table-cell; width: {{ $showVerification ? '50%' : '100%' }}; padding-left: {{ $showVerification ? '20px' : '0' }}; text-align: center;">
			<p style="font-size: 16px;">{{ __('pdfexp.certificate_teacher_label') }}</p>
			<div style="margin-top: 40px; font-weight: bold; font-size: 18px;">
				{{ $quiz->creator->name }}
			</div>
		</div>
	</div>
</body>
</html>
