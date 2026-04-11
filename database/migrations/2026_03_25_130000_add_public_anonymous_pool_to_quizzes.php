<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('is_public_anonymous_pool_mode')->default(false)->after('is_anonymous_bulk_mode');
            $table->unsignedInteger('anonymous_pool_capacity')->nullable()->after('is_public_anonymous_pool_mode');
            $table->index('is_public_anonymous_pool_mode');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['is_public_anonymous_pool_mode']);
            $table->dropColumn(['is_public_anonymous_pool_mode', 'anonymous_pool_capacity']);
        });
    }
};
