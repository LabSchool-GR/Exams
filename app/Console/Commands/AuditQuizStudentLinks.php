<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditQuizStudentLinks extends Command
{
    protected $signature = 'db:audit-quiz-student-links';

    protected $description = 'Run read-only checks for quiz_attempts.quiz_student_id integrity';

    public function handle(): int
    {
        $this->components->info('Running quiz student link audit...');

        if (! Schema::hasColumn('quiz_attempts', 'quiz_student_id')) {
            $this->newLine();
            $this->components->warn('The quiz_student_id column does not exist yet. Run the related migration before using this audit.');

            return self::SUCCESS;
        }

        $rows = DB::table('quiz_attempts as qa')
            ->leftJoin('quiz_students as linked_qs', 'linked_qs.id', '=', 'qa.quiz_student_id')
            ->leftJoin('quiz_students as expected_qs', function ($join) {
                $join->on('expected_qs.quiz_id', '=', 'qa.quiz_id')
                    ->on('expected_qs.student_code', '=', 'qa.student_code');
            })
            ->where('qa.student_code', '!=', '0000')
            ->where(function ($query) {
                $query->whereNull('qa.quiz_student_id')
                    ->orWhereNull('linked_qs.id')
                    ->orWhereColumn('linked_qs.quiz_id', '!=', 'qa.quiz_id')
                    ->orWhereColumn('linked_qs.student_code', '!=', 'qa.student_code');
            })
            ->select(
                'qa.id',
                'qa.quiz_id',
                'qa.student_code',
                'qa.quiz_student_id',
                DB::raw('expected_qs.id as expected_quiz_student_id')
            )
            ->orderBy('qa.quiz_id')
            ->orderBy('qa.student_code')
            ->limit(25)
            ->get();

        $this->newLine();
        $this->line('<options=bold>Non-guest attempts missing quiz_student_id or linked to the wrong registered student</>');

        if ($rows->isEmpty()) {
            $this->components->info('OK');

            return self::SUCCESS;
        }

        $headers = array_keys((array) $rows->first());
        $body = $rows->map(fn ($row) => array_values((array) $row))->all();

        $this->table($headers, $body);
        $this->newLine();
        $this->components->warn('Review the findings before making quiz_student_id mandatory.');

        return self::SUCCESS;
    }
}
