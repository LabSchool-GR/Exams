<?php

declare(strict_types=1);

$tag = $argv[1] ?? '';
$releaseNotesPath = $argv[2] ?? '';
$outputPath = $argv[3] ?? (__DIR__.'/../update.json');
$releaseUrl = $argv[4] ?? '';
$downloadUrl = $argv[5] ?? '';
$packageName = $argv[6] ?? '';

if ($tag === '' || $releaseNotesPath === '') {
    fwrite(STDERR, "Usage: php scripts/build-update-manifest.php <tag> <release-notes-path> [output-path] [release-url] [download-url] [package-name]\n");
    exit(1);
}

if (! is_file($releaseNotesPath)) {
    fwrite(STDERR, "Release notes file was not found.\n");
    exit(1);
}

$notes = trim((string) file_get_contents($releaseNotesPath));
$payload = [
    'version' => $tag,
    'release_name' => 'LabSchool Exams '.$tag,
    'published_at' => gmdate('c'),
    'notes' => $notes,
    'release_url' => $releaseUrl,
    'download_url' => $downloadUrl,
    'package_name' => $packageName,
];

file_put_contents(
    $outputPath,
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL
);

echo $outputPath.PHP_EOL;
