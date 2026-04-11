<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quizzes', 'is_certificate_verification_enabled')) {
            return;
        }

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->boolean('is_certificate_verification_enabled')
                ->default(false)
                ->after('is_learning_mode');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('quizzes', 'is_certificate_verification_enabled')) {
            return;
        }

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn('is_certificate_verification_enabled');
        });
    }
};
