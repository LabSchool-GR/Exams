<?php

/**
 * CspComplianceTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Illuminate\Support\Facades\File;

it('keeps blade views free from inline scripts and alpine-style inline handlers', function () {
    $patterns = [
        '/<script\b/i' => 'script tag',
        '/\bon(?:click|change|submit|load|keydown|keyup|keypress|focus|blur)=/i' => 'inline DOM event handler',
        '/\bx-data\b|\bx-init\b|\bx-on:|(?<!\w)@click\b|(?<!\w)@change\b|(?<!\w)@submit\b/i' => 'Alpine-style inline expression',
    ];

    $violations = [];

    foreach (File::allFiles(resource_path('views')) as $file) {
        $contents = File::get($file->getPathname());

        foreach ($patterns as $pattern => $label) {
            if (preg_match($pattern, $contents)) {
                $violations[] = sprintf('%s: %s', str_replace(base_path().DIRECTORY_SEPARATOR, '', $file->getPathname()), $label);
            }
        }
    }

    expect($violations)->toBeEmpty(implode(PHP_EOL, $violations));
});

it('sends an enforcing content security policy header by default', function () {
    $response = $this->get('/login');

    $response->assertHeader('Content-Security-Policy');
    expect($response->headers->has('Content-Security-Policy-Report-Only'))->toBeFalse();
    expect($response->headers->get('Content-Security-Policy'))->toContain("script-src 'self'");
    expect($response->headers->get('Content-Security-Policy'))->toContain("script-src-attr 'none'");
});
