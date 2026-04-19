<?php

use App\Casts\LegacyEncryptedString;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_students', function (Blueprint $table): void {
            $table->text('student_name')->change();
        });

        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->text('student_name')->change();
        });

        $this->encryptStudentNames('quiz_students');
        $this->encryptStudentNames('quiz_attempts');
    }

    public function down(): void
    {
        $this->decryptStudentNames('quiz_students');
        $this->decryptStudentNames('quiz_attempts');

        Schema::table('quiz_students', function (Blueprint $table): void {
            $table->string('student_name', 255)->change();
        });

        Schema::table('quiz_attempts', function (Blueprint $table): void {
            $table->string('student_name', 255)->change();
        });
    }

    private function encryptStudentNames(string $table): void
    {
        DB::table($table)
            ->select('id', 'student_name')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table): void {
                foreach ($rows as $row) {
                    if (! is_string($row->student_name) || $row->student_name === '') {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'student_name' => LegacyEncryptedString::encrypt($row->student_name),
                        ]);
                }
            });
    }

    private function decryptStudentNames(string $table): void
    {
        DB::table($table)
            ->select('id', 'student_name')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($table): void {
                foreach ($rows as $row) {
                    if (! is_string($row->student_name) || $row->student_name === '') {
                        continue;
                    }

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'student_name' => LegacyEncryptedString::decrypt($row->student_name),
                        ]);
                }
            });
    }
};
