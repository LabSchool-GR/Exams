<?php

use App\Models\Quiz;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->string('student_access_policy', 20)
                ->default(Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS)
                ->after('anonymous_pool_capacity');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropColumn('student_access_policy');
        });
    }
};
