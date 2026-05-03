# Public Update Manifest

The application can read update metadata from a public `update.json` manifest.

This is the recommended path while the canonical GitHub repository remains private.

## Why It Exists

The in-app Update Center can read the latest release directly from GitHub only when that release metadata is publicly accessible.

If the repository is private, use a separate public manifest URL instead.

## Configuration

Set this in the deployed instance:

```env
APP_UPDATE_MANIFEST_URL=https://updates.example.org/exams/update.json
```

When this URL is configured, the Update Center prefers the manifest over the GitHub API.

## Expected Format

```json
{
  "version": "v1.2.0",
  "release_name": "LabSchool Exams v1.2.0",
  "published_at": "2026-04-19T12:00:00Z",
  "notes": "# v1.2.0\n\n_Released: 2026-04-19_\n\n### Added\n- ...",
  "release_url": "https://updates.example.org/exams/releases/v1.2.0",
  "download_url": "https://updates.example.org/exams/labschool-exams-v1.2.0.zip",
  "package_name": "labschool-exams-v1.2.0.zip"
}
```

For releases that provide both a full package and an incremental upgrade package, include the structured `packages` block:

```json
{
  "version": "v2.1.0",
  "release_name": "LabSchool Exams v2.1.0",
  "published_at": "2026-05-03T12:00:00Z",
  "notes": "# v2.1.0\n\n### Added\n- Teacher-owned quiz duplication",
  "release_url": "https://updates.example.org/exams/releases/v2.1.0",
  "download_url": "https://updates.example.org/exams/labschool-exams-v2.1.0-full.zip",
  "package_name": "labschool-exams-v2.1.0-full.zip",
  "packages": {
    "full": {
      "url": "https://updates.example.org/exams/labschool-exams-v2.1.0-full.zip",
      "package_name": "labschool-exams-v2.1.0-full.zip"
    },
    "upgrades": [
      {
        "from_version": "v2.0.0",
        "to_version": "v2.1.0",
        "url": "https://updates.example.org/exams/labschool-exams-v2.0.0-to-v2.1.0-upgrade.zip",
        "package_name": "labschool-exams-v2.0.0-to-v2.1.0-upgrade.zip"
      }
    ]
  }
}
```

The in-app Update Center shows a matching upgrade package when the installed `APP_VERSION` matches an entry's `from_version`. It always keeps the full package available as a fallback.

## Workflow Support

The release workflow generates an `update.json` asset for each tagged release.

That generated file can be:

- uploaded to a public website
- mirrored to a static storage bucket
- served from a separate update endpoint outside the private repository

## Important Note

If the generated manifest keeps GitHub private release URLs in `release_url` or `download_url`, anonymous users still will not be able to access those links.

For real private-repository support, publish the manifest and the downloadable package to a public endpoint that your deployed application instances can reach.
