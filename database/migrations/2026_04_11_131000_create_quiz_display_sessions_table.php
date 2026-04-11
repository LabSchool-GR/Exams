<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_display_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_student_id')->constrained('quiz_students')->cascadeOnDelete();
            $table->foreignId('quiz_attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->string('status', 20)->default('waiting');
            $table->string('controller_session_hash', 64)->nullable();
            $table->unsignedInteger('state_version')->default(1);
            $table->timestamp('controller_claimed_at')->nullable();
            $table->timestamp('controller_last_seen_at')->nullable();
            $table->timestamp('screen_last_seen_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'status'], 'quiz_display_sessions_quiz_status_idx');
            $table->index(['quiz_student_id', 'status'], 'quiz_display_sessions_student_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_display_sessions');
    }
};
