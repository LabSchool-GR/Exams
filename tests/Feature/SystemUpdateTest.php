<?php

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    Cache::flush();

    config()->set('updates.enabled', true);
    config()->set('updates.manifest.url', null);
    config()->set('updates.github.repository', 'LabSchool-GR/Exams');
});

test('admin can view the update center using a public update manifest when configured', function () {
    config()->set('app.version', '1.0.0');
    config()->set('updates.manifest.url', 'https://updates.labschool.gr/exams/update.json');

    Http::fake([
        'https://updates.labschool.gr/exams/update.json' => Http::response([
            'version' => 'v1.2.0',
            'release_name' => 'LabSchool Exams v1.2.0',
            'published_at' => '2026-04-19T10:15:00Z',
            'notes' => "Private-repo compatible update feed\n- Public manifest enabled",
            'release_url' => 'https://updates.labschool.gr/exams/releases/v1.2.0',
            'download_url' => 'https://updates.labschool.gr/exams/labschool-exams-v1.2.0.zip',
            'package_name' => 'labschool-exams-v1.2.0.zip',
        ], 200),
        'https://api.github.com/*' => Http::response([], 500),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('system_updates.index'))
        ->assertOk()
        ->assertSee('v1.2.0')
        ->assertSee(__('system_updates.source_manifest'))
        ->assertSee('https://updates.labschool.gr/exams/update.json')
        ->assertSee('labschool-exams-v1.2.0.zip');
});

test('admin can view the update center with latest github release details', function () {
    config()->set('app.version', '1.0.0');

    Http::fake([
        'https://api.github.com/repos/LabSchool-GR/Exams/releases/latest' => Http::response([
            'name' => 'LabSchool Exams v1.1.0',
            'tag_name' => 'v1.1.0',
            'html_url' => 'https://github.com/LabSchool-GR/Exams/releases/tag/v1.1.0',
            'zipball_url' => 'https://api.github.com/repos/LabSchool-GR/Exams/zipball/v1.1.0',
            'body' => "Improvements\n- Faster polling\n- Better feedback throttling",
            'published_at' => '2026-04-19T08:00:00Z',
            'assets' => [
                [
                    'name' => 'labschool-exams-v1.1.0.zip',
                    'browser_download_url' => 'https://github.com/LabSchool-GR/Exams/releases/download/v1.1.0/labschool-exams-v1.1.0.zip',
                ],
            ],
        ], 200),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('system_updates.index'))
        ->assertOk()
        ->assertSee(__('system_updates.heading'))
        ->assertSee('1.0.0')
        ->assertSee('v1.1.0')
        ->assertSee(__('system_updates.status_update_available', ['version' => 'v1.1.0']))
        ->assertSee('labschool-exams-v1.1.0.zip')
        ->assertSee(__('system_updates.download_package'));
});

test('non admin users cannot access the update center', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($teacher)
        ->get(route('system_updates.index'))
        ->assertForbidden();
});

test('update center shows a friendly status when github release check fails', function () {
    config()->set('app.version', '1.0.0');

    Http::fake([
        'https://api.github.com/repos/LabSchool-GR/Exams/releases/latest' => Http::response([
            'message' => 'Server error',
        ], 500),
    ]);

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('system_updates.index'))
        ->assertOk()
        ->assertSee(__('system_updates.status_error'));
});
