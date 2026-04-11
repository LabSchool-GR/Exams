<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_students', function (Blueprint $table) {
            $table->boolean('is_anonymous')->default(false)->after('max_attempts');
            $table->index(['quiz_id', 'is_anonymous']);
        });
    }

    public function down(): void
    {
        Schema::table('quiz_students', function (Blueprint $table) {
            $table->dropIndex(['quiz_id', 'is_anonymous']);
            $table->dropColumn('is_anonymous');
        });
    }
};
