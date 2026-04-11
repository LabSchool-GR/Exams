<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTokensToQuizzesAndStudents extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('allow_guest');
            $table->string('public_token', 32)->nullable()->unique()->after('is_public');
        });

        Schema::table('quiz_students', function (Blueprint $table) {
            $table->string('access_token', 32)->nullable()->unique()->after('student_code');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['is_public', 'public_token']);
        });

        Schema::table('quiz_students', function (Blueprint $table) {
            $table->dropColumn(['access_token']);
        });
    }
}
