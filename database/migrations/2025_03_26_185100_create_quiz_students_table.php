<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quiz_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->onDelete('cascade');
            $table->string('student_code', 4);
            $table->string('student_name');
            $table->unsignedSmallInteger('max_attempts')->default(1);
            $table->timestamps();

            $table->unique(['quiz_id', 'student_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_students');
    }
};
