<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
	public function up()
	{
		Schema::table('quizzes', function (Blueprint $table) {
			$table->boolean('has_timer')->default(true)->after('time_limit'); // ⏱️
			$table->boolean('allow_resume')->default(true)->after('has_timer'); // 🔁
		});
	}

	public function down()
	{
		Schema::table('quizzes', function (Blueprint $table) {
			$table->dropColumn(['has_timer', 'allow_resume']);
		});
	}
};
