<?php

declare(strict_types=1);

$tag = $argv[1] ?? '';
$changelogPath = __DIR__ . '/../CHANGELOG.md';
$outputPath = $argv[2] ?? (__DIR__ . '/../release-notes.md');

if ($tag === '') {
    fwrite(STDERR, "Usage: php scripts/build-release-notes.php <tag> [output-path]\n");
    exit(1);
}

if (! is_file($changelogPath)) {
    fwrite(STDERR, "CHANGELOG.md was not found.\n");
    exit(1);
}

$contents = (string) file_get_contents($changelogPath);
$escapedTag = preg_quote($tag, '/');
$pattern = '/^##\s+\[' . $escapedTag . '\]\s*-\s*([0-9]{4}-[0-9]{2}-[0-9]{2})\R(.*?)(?=^##\s+\[|\z)/ms';

if (preg_match($pattern, $contents, $matches) === 1) {
    $date = trim($matches[1]);
    $body = trim($matches[2]);

    $releaseNotes = "# {$tag}\n\n";
    $releaseNotes .= "_Released: {$date}_\n\n";
    $releaseNotes .= $body . "\n";
} else {
    $releaseNotes = "# {$tag}\n\n";
    $releaseNotes .= "Curated release notes were not prepared for this tag.\n\n";
    $releaseNotes .= "See `CHANGELOG.md` for the maintained project history.\n";
}

file_put_contents($outputPath, $releaseNotes);
echo $outputPath . PHP_EOL;
