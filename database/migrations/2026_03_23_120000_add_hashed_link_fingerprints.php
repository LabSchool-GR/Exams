<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('public_token_hash', 64)->nullable()->after('public_token')->unique();
        });

        Schema::table('quiz_students', function (Blueprint $table) {
            $table->string('access_token_hash', 64)->nullable()->after('access_token')->unique();
        });

        DB::table('quizzes')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($quizzes): void {
                foreach ($quizzes as $quiz) {
                    DB::table('quizzes')
                        ->where('id', $quiz->id)
                        ->update([
                            'public_token_hash' => hash('sha256', Str::random(64)),
                            'public_token' => null,
                        ]);
                }
            });

        DB::table('quiz_students')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($students): void {
                foreach ($students as $student) {
                    DB::table('quiz_students')
                        ->where('id', $student->id)
                        ->update([
                            'access_token_hash' => hash('sha256', Str::random(64)),
                            'access_token' => null,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('quiz_students', function (Blueprint $table) {
            $table->dropUnique(['access_token_hash']);
            $table->dropColumn('access_token_hash');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropUnique(['public_token_hash']);
            $table->dropColumn('public_token_hash');
        });
    }
};
