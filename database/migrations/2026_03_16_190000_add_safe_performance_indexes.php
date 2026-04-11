<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Supports lookups by quiz/student and submitted/unsubmitted attempt state.
            $table->index(['quiz_id', 'student_code'], 'quiz_attempts_quiz_student_idx');
            $table->index(['quiz_id', 'submitted_at'], 'quiz_attempts_quiz_submitted_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            // Supports ordered question retrieval inside a quiz.
            $table->index(['quiz_id', 'order'], 'questions_quiz_order_idx');
        });

        Schema::table('answers', function (Blueprint $table) {
            // Supports correct-answer filtering per question.
            $table->index(['question_id', 'is_correct'], 'answers_question_correct_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('quiz_attempts_quiz_student_idx');
            $table->dropIndex('quiz_attempts_quiz_submitted_idx');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex('questions_quiz_order_idx');
        });

        Schema::table('answers', function (Blueprint $table) {
            $table->dropIndex('answers_question_correct_idx');
        });
    }
};
