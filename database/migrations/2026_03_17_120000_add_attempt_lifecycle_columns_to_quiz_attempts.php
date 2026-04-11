<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('submitted_at');
            $table->timestamp('expires_at')->nullable()->after('started_at');
            $table->timestamp('finalized_at')->nullable()->after('expires_at');
            $table->timestamp('last_seen_at')->nullable()->after('finalized_at');
            $table->string('status', 20)->default('in_progress')->after('score');
            $table->string('finish_reason', 30)->nullable()->after('status');
            $table->unsignedInteger('current_question_index')->default(0)->after('max_attempts');
            $table->json('question_order')->nullable()->after('current_question_index');
            $table->json('answer_order')->nullable()->after('question_order');
            $table->json('skipped_question_ids')->nullable()->after('answer_order');

            $table->index(['status', 'expires_at'], 'quiz_attempts_status_expires_idx');
            $table->index(['quiz_id', 'status'], 'quiz_attempts_quiz_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropIndex('quiz_attempts_status_expires_idx');
            $table->dropIndex('quiz_attempts_quiz_status_idx');

            $table->dropColumn([
                'started_at',
                'expires_at',
                'finalized_at',
                'last_seen_at',
                'status',
                'finish_reason',
                'current_question_index',
                'question_order',
                'answer_order',
                'skipped_question_ids',
            ]);
        });
    }
};
