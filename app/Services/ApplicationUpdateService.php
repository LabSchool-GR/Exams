<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class ApplicationUpdateService
{
    /**
     * Return the current update-check status for the configured GitHub repository.
     *
     * @return array<string, mixed>
     */
    public function getStatus(): array
    {
        $currentVersion = $this->currentVersion();
        $currentNormalized = $this->normalizeVersion($currentVersion);
        $manifestUrl = $this->manifestUrl();
        $repository = $this->repository();

        $status = [
            'enabled' => (bool) config('updates.enabled', true),
            'configured' => false,
            'source' => null,
            'manifest_url' => $manifestUrl,
            'repository' => $repository,
            'current_version' => $currentVersion,
            'current_version_normalized' => $currentNormalized,
            'current_version_comparable' => $currentNormalized !== null,
            'latest_release' => null,
            'status' => 'not_configured',
            'checked_at' => null,
            'error' => null,
        ];

        if (! $status['enabled']) {
            $status['status'] = 'disabled';

            return $status;
        }

        if (! filled($manifestUrl) && ! filled($repository)) {
            return $status;
        }

        $status['configured'] = true;

        try {
            $sourceType = filled($manifestUrl) ? 'manifest' : 'github';
            $sourceKey = filled($manifestUrl) ? $manifestUrl : (string) $repository;

            $latestRelease = Cache::remember(
                $this->cacheKey($sourceType, $sourceKey),
                now()->addMinutes((int) config('updates.github.cache_ttl_minutes', 30)),
                fn (): array => filled($manifestUrl)
                    ? $this->fetchManifestRelease($manifestUrl)
                    : $this->fetchLatestGithubRelease((string) $repository),
            );

            $status['source'] = $sourceType;
            $status['latest_release'] = $latestRelease;
            $status['checked_at'] = $latestRelease['checked_at'];
            $status['status'] = $this->resolveStatus(
                $status['current_version_normalized'],
                $latestRelease['normalized_version'] ?? null,
            );
        } catch (Throwable $exception) {
            $status['status'] = 'error';
            $status['checked_at'] = now()->toIso8601String();
            $status['error'] = config('app.debug') ? $exception->getMessage() : null;
        }

        return $status;
    }

    /**
     * Clear the cached update metadata for the configured repository.
     */
    public function forgetCachedRelease(): void
    {
        $manifestUrl = $this->manifestUrl();
        $repository = $this->repository();

        if (filled($manifestUrl)) {
            Cache::forget($this->cacheKey('manifest', $manifestUrl));
        }

        if (filled($repository)) {
            Cache::forget($this->cacheKey('github', $repository));
        }
    }

    /**
     * Fetch the latest release metadata from a public update manifest.
     *
     * @return array<string, mixed>
     */
    private function fetchManifestRelease(string $manifestUrl): array
    {
        $response = Http::acceptJson()
            ->timeout((int) config('updates.github.timeout_seconds', 5))
            ->get($manifestUrl);

        $response->throw();

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        $version = trim((string) data_get($payload, 'version', ''));

        if ($version === '') {
            throw new RuntimeException('The update manifest is missing a version field.');
        }

        $publishedAtRaw = data_get($payload, 'published_at');
        $publishedAt = is_string($publishedAtRaw) && filled($publishedAtRaw)
            ? CarbonImmutable::parse($publishedAtRaw)
            : null;

        return [
            'name' => trim((string) data_get($payload, 'release_name', '')) ?: $version,
            'version' => $version,
            'normalized_version' => $this->normalizeVersion($version),
            'published_at' => $publishedAt?->toIso8601String(),
            'published_at_label' => $publishedAt?->setTimezone(config('app.timezone', 'UTC'))->format('d/m/Y H:i'),
            'notes' => trim((string) data_get($payload, 'notes', '')),
            'url' => trim((string) data_get($payload, 'release_url', '')),
            'download_url' => trim((string) data_get($payload, 'download_url', '')),
            'download_name' => trim((string) data_get($payload, 'package_name', '')),
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Fetch the latest GitHub release metadata from the configured repository.
     *
     * @return array<string, mixed>
     */
    private function fetchLatestGithubRelease(string $repository): array
    {
        $response = Http::baseUrl((string) config('updates.github.api_base_url', 'https://api.github.com'))
            ->acceptJson()
            ->timeout((int) config('updates.github.timeout_seconds', 5))
            ->withHeaders([
                'User-Agent' => config('app.name', 'Laravel') . ' Update Center',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->get('/repos/' . trim($repository, '/') . '/releases/latest');

        if ($response->status() === 404) {
            throw new RuntimeException('No published GitHub release was found.');
        }

        $response->throw();

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        $tagName = trim((string) data_get($payload, 'tag_name', ''));
        $publishedAtRaw = data_get($payload, 'published_at');
        $publishedAt = is_string($publishedAtRaw) && filled($publishedAtRaw)
            ? CarbonImmutable::parse($publishedAtRaw)
            : null;

        $asset = collect(data_get($payload, 'assets', []))
            ->first(function (mixed $asset): bool {
                if (! is_array($asset)) {
                    return false;
                }

                $name = Str::lower((string) data_get($asset, 'name', ''));
                $url = (string) data_get($asset, 'browser_download_url', '');

                return filled($url) && Str::endsWith($name, '.zip');
            });

        return [
            'name' => trim((string) data_get($payload, 'name', '')) ?: $tagName,
            'version' => $tagName,
            'normalized_version' => $this->normalizeVersion($tagName),
            'published_at' => $publishedAt?->toIso8601String(),
            'published_at_label' => $publishedAt?->setTimezone(config('app.timezone', 'UTC'))->format('d/m/Y H:i'),
            'notes' => trim((string) data_get($payload, 'body', '')),
            'url' => (string) data_get($payload, 'html_url', ''),
            'download_url' => is_array($asset)
                ? (string) data_get($asset, 'browser_download_url', '')
                : (string) data_get($payload, 'zipball_url', ''),
            'download_name' => is_array($asset)
                ? trim((string) data_get($asset, 'name', ''))
                : '',
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Return the configured public update manifest URL when present.
     */
    private function manifestUrl(): ?string
    {
        $manifestUrl = trim((string) config('updates.manifest.url', ''));

        return $manifestUrl !== '' ? $manifestUrl : null;
    }

    /**
     * Resolve the current update status using the installed and latest comparable versions.
     */
    private function resolveStatus(?string $currentVersion, ?string $latestVersion): string
    {
        if (! filled($latestVersion)) {
            return 'release_unavailable';
        }

        if (! filled($currentVersion)) {
            return 'comparison_unavailable';
        }

        if (version_compare($latestVersion, $currentVersion, '>')) {
            return 'update_available';
        }

        if (version_compare($currentVersion, $latestVersion, '>')) {
            return 'ahead_of_latest';
        }

        return 'up_to_date';
    }

    /**
     * Normalize a version string so it can be compared with version_compare().
     */
    private function normalizeVersion(string $version): ?string
    {
        $normalized = ltrim(trim($version), 'vV');

        if ($normalized === '' || preg_match('/^\d/', $normalized) !== 1) {
            return null;
        }

        return $normalized;
    }

    /**
     * Resolve the configured GitHub repository slug.
     */
    private function repository(): ?string
    {
        $configuredRepository = trim((string) config('updates.github.repository', ''));

        if ($configuredRepository !== '') {
            return trim($configuredRepository, '/');
        }

        $sourceUrl = trim((string) config('app.source_url', ''));

        if ($sourceUrl === '') {
            return null;
        }

        $path = trim((string) parse_url($sourceUrl, PHP_URL_PATH), '/');
        $segments = array_values(array_filter(explode('/', $path)));

        if (count($segments) < 2) {
            return null;
        }

        return $segments[0] . '/' . preg_replace('/\.git$/', '', $segments[1]);
    }

    /**
     * Build the cache key for the repository-specific latest release metadata.
     */
    private function cacheKey(string $sourceType, string $sourceKey): string
    {
        return 'system-updates.release.' . $sourceType . '.' . md5($sourceKey);
    }

    /**
     * Return the current application version label.
     */
    private function currentVersion(): string
    {
        return trim((string) config('app.version', 'dev')) ?: 'dev';
    }
}
