<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard against duplicate schema history: the column already exists in older installs.
        if (! Schema::hasColumn('quizzes', 'is_random_answers_order')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->boolean('is_random_answers_order')
                    ->default(false)
                    ->after('is_random_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quizzes', 'is_random_answers_order')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('is_random_answers_order');
            });
        }
    }
};
