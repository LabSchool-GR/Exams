<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('quizzes') && Schema::hasColumn('quizzes', 'question_view')) {
            DB::table('quizzes')
                ->where('question_view', 'retroAXD3_img')
                ->update(['question_view' => 'default_img']);
        }

        if (Schema::hasTable('quiz_templates')) {
            DB::table('quiz_templates')
                ->where('code', 'retroAXD3_img')
                ->delete();
        }
    }

    public function down(): void
    {
        // The retired template and its executable views are intentionally not restored.
    }
};
