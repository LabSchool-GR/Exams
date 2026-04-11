<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quizzes', 'is_learning_mode')) {
            return;
        }

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->boolean('is_learning_mode')
                ->default(false)
                ->after('allow_resume');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('quizzes', 'is_learning_mode')) {
            return;
        }

        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn('is_learning_mode');
        });
    }
};
