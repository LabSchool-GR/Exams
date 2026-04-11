<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <title>{{ __('pdfexp.students_list_title') ?? 'Κατάλογος Μαθητών' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13.5px;
            line-height: 1.6;
            color: #000;
            padding: 40px;
        }

        h1 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 25px;
        }

        .section {
            border: 1px solid #ddd;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .section h2 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .info-label {
            font-weight: bold;
            color: #444;
            width: 180px;
        }

        .info-value {
            color: #000;
        }

        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .students-table th,
        .students-table td {
            border: 1px solid #333;
            padding: 6px;
            font-size: 12px;
        }

        .students-table th {
            background-color: #e9ecef;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div style="text-align: right; font-size: 12px; color: #555;">
        {{ __('pdfexp.issue_date') ?? 'Ημερομηνία έκδοσης:' }} {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>

    <h1>{{ __('pdfexp.students_list_title') ?? 'Κατάλογος Καταχωρημένων Μαθητών' }}</h1>

    <div class="section">
        <h2>{{ __('pdfexp.quiz_info') }}</h2>
        <table class="info-table">
            <tr>
                <td class="info-label">{{ __('pdfexp.quiz_title') }}:</td>
                <td class="info-value">{{ $quiz->title }}</td>
            </tr>
            <tr>
                <td class="info-label">{{ __('pdfexp.quiz_code') }}:</td>
                <td class="info-value">{{ $quiz->quiz_code }}</td>
            </tr>
            <tr>
                <td class="info-label">{{ __('pdfexp.quiz_teacher') }}:</td>
                <td class="info-value">{{ $quiz->creator->name ?? '—' }}</td>
            </tr>
        </table>
    </div>

    <h2 style="font-size: 16px; margin-bottom: 10px; color: #333;">
        {{ __('pdfexp.student_list') ?? 'Λίστα Μαθητών' }}
    </h2>

    <table class="students-table" style="margin-bottom: 30px;">
        <thead>
            <tr>
                <th>#</th>
                <th>{{ __('pdfexp.student_name') }}</th>
                <th>{{ __('pdfexp.student_code') }}</th>
                <th>{{ __('pdfexp.max_attempts') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $entry)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $entry['name'] }}</td>
                    <td class="text-center">{{ $entry['code'] }}</td>
                    <td class="text-center">{{ $entry['max_attempts'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
