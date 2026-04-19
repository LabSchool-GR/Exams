<?php

// database/migrations/2025_07_05_100000_create_quiz_templates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();           // π.χ. default, exakoustou_img
            $table->string('name');                     // φιλικό όνομα π.χ. "Βασικό", "Εξακουστού Image"
            $table->string('description')->nullable();  // σύντομη περιγραφή
            $table->boolean('is_common')->default(false); // κοινό template για όλους τους χρήστες
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_templates');
    }
};
