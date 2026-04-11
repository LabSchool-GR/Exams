<?php

use App\Casts\LegacyEncryptedString;
use App\Models\QuizAttempt;
use App\Support\StudentNameBlindIndex;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_students', function (Blueprint $table) {
            $table->text('student_name_blind_index')->nullable()->after('student_name');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->text('student_name_blind_index')->nullable()->after('student_name');
        });

        $this->backfillStudents();
        $this->backfillAttempts();
    }

    public function down(): void
    {
        Schema::table('quiz_students', function (Blueprint $table) {
            $table->dropColumn('student_name_blind_index');
        });

        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('student_name_blind_index');
        });
    }

    private function backfillStudents(): void
    {
        DB::table('quiz_students')
            ->select('id', 'student_name')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $name = LegacyEncryptedString::decrypt($row->student_name);
                    $index = StudentNameBlindIndex::forValue($name);

                    DB::table('quiz_students')
                        ->where('id', $row->id)
                        ->update(['student_name_blind_index' => $index]);
                }
            });
    }

    private function backfillAttempts(): void
    {
        DB::table('quiz_attempts')
            ->select('id', 'student_name', 'student_code')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    $name = LegacyEncryptedString::decrypt($row->student_name);
                    $index = ($row->student_code === QuizAttempt::ANONYMIZED_STUDENT_CODE && $name === QuizAttempt::ANONYMIZED_STUDENT_NAME)
                        ? null
                        : StudentNameBlindIndex::forValue($name);

                    DB::table('quiz_attempts')
                        ->where('id', $row->id)
                        ->update(['student_name_blind_index' => $index]);
                }
            });
    }
};