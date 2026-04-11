<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->foreignId('quiz_student_id')
                ->nullable()
                ->after('quiz_id')
                ->constrained('quiz_students')
                ->nullOnDelete();

            $table->index(['quiz_student_id', 'submitted_at'], 'quiz_attempts_student_submitted_idx');
        });

        // Backfill the new relation for non-guest attempts using the existing quiz_id/student_code pair.
        DB::table('quiz_attempts')
            ->where('student_code', '!=', '0000')
            ->orderBy('id')
            ->chunkById(500, function ($attempts): void {
                foreach ($attempts as $attempt) {
                    $quizStudentId = DB::table('quiz_students')
                        ->where('quiz_id', $attempt->quiz_id)
                        ->where('student_code', $attempt->student_code)
                        ->value('id');

                    if ($quizStudentId) {
                        DB::table('quiz_attempts')
                            ->where('id', $attempt->id)
                            ->update(['quiz_student_id' => $quizStudentId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('quiz_attempts_student_submitted_idx');
            $table->dropConstrainedForeignId('quiz_student_id');
        });
    }
};
