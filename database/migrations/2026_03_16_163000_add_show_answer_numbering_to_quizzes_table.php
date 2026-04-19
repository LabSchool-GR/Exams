<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard against duplicate schema history: the column already exists in older installs.
        if (! Schema::hasColumn('quizzes', 'show_answer_numbering')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->boolean('show_answer_numbering')
                    ->default(false)
                    ->after('is_random_answers_order');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('quizzes', 'show_answer_numbering')) {
            Schema::table('quizzes', function (Blueprint $table) {
                $table->dropColumn('show_answer_numbering');
            });
        }
    }
};
