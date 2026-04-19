<?php

// database/migrations/2025_07_05_100010_create_quiz_template_user_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_template_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_template_id')->constrained('quiz_templates')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['quiz_template_id', 'user_id']); // κάθε χρήστης μία φορά ανά template
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_template_user');
    }
};
