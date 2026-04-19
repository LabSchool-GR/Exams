<?php

use App\Models\Quiz;
use App\Models\QuizTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

test('database seeder keeps the default baseline limited to core data', function () {
    Artisan::call('db:seed');

    expect(QuizTemplate::query()->count())->toBeGreaterThan(0)
        ->and(Quiz::query()->where('is_system_example', true)->exists())->toBeFalse()
        ->and(User::query()->where('email', 'admin@labschool.sch.gr')->exists())->toBeFalse();
});

test('install command bootstraps the app with explicit demo and admin options', function () {
    $exitCode = Artisan::call('app:install', [
        '--admin-email' => 'admin@example.test',
        '--admin-name' => 'Install Admin',
        '--admin-password' => 'super-secure-password',
        '--demo' => true,
        '--skip-storage-link' => true,
    ]);

    $installedAdmin = User::query()->where('email', 'admin@example.test')->first();

    expect($exitCode)->toBe(0)
        ->and($installedAdmin)->not->toBeNull()
        ->and(Hash::check('super-secure-password', $installedAdmin->password))->toBeTrue()
        ->and(QuizTemplate::query()->count())->toBeGreaterThan(0)
        ->and(Quiz::query()->where('is_system_example', true)->exists())->toBeTrue();
});
