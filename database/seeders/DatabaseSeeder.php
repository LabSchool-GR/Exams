<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(CoreSeeder::class);

        if ($this->seedDemoDataEnabled()) {
            $this->call(DemoSeeder::class);
        }

        if ($this->seedDevDataEnabled()) {
            $this->call(DevSeeder::class);
        }
    }

    private function seedDemoDataEnabled(): bool
    {
        return (bool) filter_var((string) env('APP_SEED_DEMO', false), FILTER_VALIDATE_BOOL);
    }

    private function seedDevDataEnabled(): bool
    {
        return (bool) filter_var((string) env('APP_SEED_DEV', false), FILTER_VALIDATE_BOOL);
    }
}
