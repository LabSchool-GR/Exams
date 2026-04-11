<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_anonymous_bulk_mode')->default(false)->after('is_public');
            $table->index('is_anonymous_bulk_mode');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['is_anonymous_bulk_mode']);
            $table->dropColumn('is_anonymous_bulk_mode');
        });
    }
};
