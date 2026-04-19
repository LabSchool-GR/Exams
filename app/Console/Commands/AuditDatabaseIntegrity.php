<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditDatabaseIntegrity extends Command
{
    protected $signature = 'db:audit-integrity';

    protected $description = 'Run read-only integrity checks before stricter database migrations';

    public function handle(): int
    {
        $this->components->info('Running database integrity audit...');

        $issuesFound = false;

        $issuesFound = $this->reportRows(
            'Attempts without a matching registered student (excluding guest attempts)',
            DB::table('quiz_attempts as qa')
                ->leftJoin('quiz_students as qs', function ($join) {
                    $join->on('qs.quiz_id', '=', 'qa.quiz_id')
                        ->on('qs.student_code', '=', 'qa.student_code');
                })
                ->where('qa.student_code', '!=', '0000')
                ->whereNull('qs.id')
                ->select('qa.id', 'qa.quiz_id', 'qa.student_code', 'qa.submitted_at')
                ->orderBy('qa.quiz_id')
                ->orderBy('qa.student_code')
                ->limit(25)
                ->get(),
            $issuesFound
        );

        $issuesFound = $this->reportRows(
            'Duplicate attempt-answer rows for the same attempt/question/answer triple',
            DB::table('quiz_attempt_answers')
                ->select(
                    'attempt_id',
                    'question_id',
                    'answer_id',
                    DB::raw('COUNT(*) as duplicate_count')
                )
                ->groupBy('attempt_id', 'question_id', 'answer_id')
                ->havingRaw('COUNT(*) > 1')
                ->orderByDesc('duplicate_count')
                ->limit(25)
                ->get(),
            $issuesFound
        );

        $issuesFound = $this->reportRows(
            'Questions where stored correct_answers_count does not match actual correct answers',
            DB::table('questions as q')
                ->leftJoin('answers as a', 'a.question_id', '=', 'q.id')
                ->select(
                    'q.id',
                    'q.quiz_id',
                    'q.correct_answers_count',
                    DB::raw('SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END) as actual_correct_answers')
                )
                ->groupBy('q.id', 'q.quiz_id', 'q.correct_answers_count')
                ->havingRaw('q.correct_answers_count <> SUM(CASE WHEN a.is_correct = 1 THEN 1 ELSE 0 END)')
                ->orderBy('q.quiz_id')
                ->orderBy('q.id')
                ->limit(25)
                ->get(),
            $issuesFound
        );

        $issuesFound = $this->reportRows(
            'Quiz attempts whose score is outside the expected 0-100 range',
            DB::table('quiz_attempts')
                ->where('score', '<', 0)
                ->orWhere('score', '>', 100)
                ->select('id', 'quiz_id', 'student_code', 'score')
                ->orderBy('quiz_id')
                ->limit(25)
                ->get(),
            $issuesFound
        );

        if (! $issuesFound) {
            $this->components->info('No blocking integrity issues were found by this audit.');
        } else {
            $this->newLine();
            $this->components->warn('Review the findings before applying stricter foreign keys or unique constraints.');
        }

        return self::SUCCESS;
    }

    /**
     * Print a compact table when findings exist, otherwise print a success line.
     */
    private function reportRows(string $title, $rows, bool $issuesFound): bool
    {
        $this->newLine();
        $this->line("<options=bold>{$title}</>");

        if ($rows->isEmpty()) {
            $this->components->info('OK');

            return $issuesFound;
        }

        $firstRow = (array) $rows->first();
        $headers = array_keys($firstRow);
        $body = $rows->map(fn ($row) => array_values((array) $row))->all();

        $this->table($headers, $body);

        return true;
    }
}
