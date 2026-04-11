<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('quiz_code', 8)->unique();
            $table->integer('max_attempts')->default(1);
            $table->integer('time_limit')->default(600); // 10 λεπτά
            $table->boolean('is_random_order')->default(false);
            $table->boolean('is_random_answers_order')->default(false);
            $table->boolean('show_answer_numbering')->default(false);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('quizzes');
    }
};
