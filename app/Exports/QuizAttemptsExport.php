<?php

/**
 * QuizAttemptsExport.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Exports;

use App\Models\Quiz;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\BeforeSheet;

class QuizAttemptsExport implements FromArray, WithEvents, WithHeadings
{
    protected Quiz $quiz;

    protected array $rows = [];

    public function __construct(int $quizId)
    {
        $this->quiz = Quiz::with('attempts')->findOrFail($quizId);

        $groupedAttempts = $this->quiz->attempts
            ->sortBy('created_at')
            ->groupBy(function ($attempt) {
                // Prefer the relational key for new attempts and keep a legacy fallback.
                return $attempt->quiz_student_id
                    ? 'student:'.$attempt->quiz_student_id
                    : 'code:'.$attempt->student_code;
            });

        foreach ($groupedAttempts as $studentAttempts) {
            foreach ($studentAttempts as $index => $attempt) {
                $this->rows[] = [
                    $attempt->student_name,
                    $attempt->student_code,
                    $index + 1,
                    $attempt->score.'%',
                    $attempt->submitted_at,
                    $attempt->created_at,
                    $attempt->updated_at,
                ];
            }
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            ['Quiz Title:', $this->quiz->title],
            [],
            [
                'Student Name',
                'Student Code',
                'Attempt #',
                'Score (%)',
                'Submitted At',
                'Created At',
                'Last Updated',
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event): void {
                $event->sheet
                    ->getDelegate()
                    ->getStyle('A1:B1')
                    ->getFont()
                    ->setBold(true);
            },
        ];
    }
}
