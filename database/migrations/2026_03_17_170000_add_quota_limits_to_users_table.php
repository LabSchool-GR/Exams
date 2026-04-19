<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedSmallInteger('max_quizzes')->default(1)->after('role');
            $table->unsignedSmallInteger('max_questions_per_quiz')->default(30)->after('max_quizzes');
            $table->unsignedSmallInteger('max_answers_per_question')->default(4)->after('max_questions_per_quiz');
            $table->unsignedSmallInteger('max_students_per_quiz')->default(30)->after('max_answers_per_question');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'max_quizzes',
                'max_questions_per_quiz',
                'max_answers_per_question',
                'max_students_per_quiz',
            ]);
        });
    }
};
