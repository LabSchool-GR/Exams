<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->boolean('is_system_example')
                ->default(false)
                ->after('image');
            $table->string('system_key')
                ->nullable()
                ->unique()
                ->after('is_system_example');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table): void {
            $table->dropUnique(['system_key']);
            $table->dropColumn(['is_system_example', 'system_key']);
        });
    }
};
