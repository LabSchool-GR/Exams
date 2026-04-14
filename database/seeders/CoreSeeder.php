<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        // Core seeding stays idempotent and includes only baseline records.
        $this->call([
            QuizTemplateSeeder::class,
        ]);
    }
}
