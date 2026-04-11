<?php

/**
 * OperationalCleanupTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

it('trims local runtime artifacts without removing sentinel files', function () {
    $viewsPath = storage_path('framework/views');
    $cacheDataPath = storage_path('framework/cache/data');
    $logPath = storage_path('framework/testing/operational-cleanup.log');

    $compiledViewPath = $viewsPath . DIRECTORY_SEPARATOR . 'operational-cleanup-test.php';
    $cacheBucketPath = $cacheDataPath . DIRECTORY_SEPARATOR . 'aa' . DIRECTORY_SEPARATOR . 'bb';
    $cacheFilePath = $cacheBucketPath . DIRECTORY_SEPARATOR . 'operational-cleanup-test';
    $originalLogContents = File::exists($logPath) ? File::get($logPath) : null;

    File::ensureDirectoryExists($viewsPath);
    File::ensureDirectoryExists($cacheBucketPath);
    File::ensureDirectoryExists(dirname($logPath));

    File::put($compiledViewPath, '<?php echo "compiled";');
    File::put($cacheFilePath, 'cached');
    File::put($logPath, str_repeat('L', 2 * 1024 * 1024));

    try {
        Artisan::call('local:trim-runtime-artifacts', [
            '--log-megabytes' => 1,
            '--log-path' => $logPath,
        ]);

        clearstatcache(true, $logPath);

        expect(File::exists($compiledViewPath))->toBeFalse()
            ->and(File::exists($cacheFilePath))->toBeFalse()
            ->and(File::exists($viewsPath . DIRECTORY_SEPARATOR . '.gitignore'))->toBeTrue()
            ->and(File::size($logPath))->toBe(0);
    } finally {
        File::delete($compiledViewPath);
        File::delete($cacheFilePath);

        if ($originalLogContents !== null) {
            File::put($logPath, $originalLogContents);
        } else {
            File::delete($logPath);
        }
    }
});
