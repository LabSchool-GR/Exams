<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->text('text');
            $table->string('image')->nullable();
            $table->integer('correct_answers_count')->default(1);
            $table->integer('order')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('questions');
    }
};
