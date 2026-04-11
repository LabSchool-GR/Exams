<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            // Prevent duplicate selections for the same answer inside one attempt/question pair.
            $table->unique(
                ['attempt_id', 'question_id', 'answer_id'],
                'quiz_attempt_answers_attempt_question_answer_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempt_answers', function (Blueprint $table) {
            $table->dropUnique('quiz_attempt_answers_attempt_question_answer_unique');
        });
    }
};
