<?php

test('public documentation has no retired sponsorship links or unsupported theme color metadata', function () {
    $docsPath = base_path('docs');
    $htmlFiles = glob($docsPath.'/*.html') ?: [];

    expect(file_exists($docsPath.'/sponsor.html'))->toBeFalse()
        ->and($htmlFiles)->not->toBeEmpty();

    $filesToInspect = [
        base_path('README.md'),
        ...$htmlFiles,
        $docsPath.'/assets/pages.css',
        $docsPath.'/assets/pages.js',
    ];

    foreach ($filesToInspect as $file) {
        $contents = file_get_contents($file);

        expect($contents)
            ->not->toContain('sponsor.html')
            ->not->toContain('paypal.com/donate')
            ->not->toContain('name="theme-color"');
    }
});

test('public documentation local links and assets resolve', function () {
    $htmlFiles = glob(base_path('docs/*.html')) ?: [];
    $brokenReferences = [];

    foreach ($htmlFiles as $file) {
        $contents = file_get_contents($file);
        preg_match_all('/(?:href|src)="([^"]+)"/', $contents, $matches);

        foreach ($matches[1] as $reference) {
            if (preg_match('/^(?:https?:|mailto:|tel:|#|data:|javascript:)/', $reference)) {
                continue;
            }

            $relativePath = preg_split('/[?#]/', $reference, 2)[0];

            if ($relativePath !== '' && ! file_exists(dirname($file).'/'.$relativePath)) {
                $brokenReferences[] = basename($file).' -> '.$reference;
            }
        }
    }

    expect($brokenReferences)->toBeEmpty();
});
