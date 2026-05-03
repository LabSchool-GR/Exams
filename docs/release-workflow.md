# Release Workflow

The project includes a tag-based GitHub Actions release workflow at [.github/workflows/release.yml](../.github/workflows/release.yml).

## How To Publish a Release

1. Make sure the `main` branch is green in CI.
2. Create a semantic version tag such as `v1.2.0`.
3. Push the tag to GitHub.

Example:

```bash
git tag v1.2.0
git push origin v1.2.0
```

## What the Workflow Produces

For each pushed tag:

- validates `composer.json`
- installs production Composer dependencies
- installs frontend dependencies
- builds `public/build`
- creates a release zip package
- creates an incremental upgrade zip package for the configured upgrade base tag
- writes a `VERSION` file inside the package using the tag name
- builds curated release notes from `CHANGELOG.md` when a matching tag section exists
- uploads the zip and its SHA-256 checksum to the GitHub Release
- publishes the resulting release notes to GitHub

## Package Contents

The release package is intended for administrator download through the in-app Update Center.

It includes:

- application source code
- `vendor/` with production dependencies
- built frontend assets under `public/build`
- the committed schema dump and migrations
- a top-level `VERSION` file with the release tag
- an `update.json` asset generated from the tag and curated release notes

It excludes:

- `.git/`
- `.github/`
- `node_modules/`
- `tests/`
- local `.env`
- transient runtime logs and cache/view/session files

## Upgrade Package

Starting with `v2.1.0`, the workflow also publishes an incremental package for the configured upgrade base tag.

For the first 2.1 release, that path is:

```text
v2.0.0 -> v2.1.0
```

The package is named:

```text
labschool-exams-v2.0.0-to-v2.1.0-upgrade.zip
```

See [upgrade-packages.md](upgrade-packages.md) for the package contents and operator steps.

## Version Display

If `APP_VERSION` is not explicitly set in `.env`, the application can fall back to the packaged `VERSION` file. This keeps the footer and Update Center aligned with releases installed from the downloadable zip package.

## Release Notes in the Update Center

The Update Center reads the latest published GitHub Release. Its changelog panel is populated from the GitHub Release body.

If a matching `## [vX.Y.Z]` section exists in [CHANGELOG.md](../CHANGELOG.md), the release workflow uses that section as the published release notes.

## Private Repository Limitation

While the canonical GitHub repository is private, the current in-app Update Center cannot read latest release metadata anonymously from the GitHub API.

This means:

- the release workflow can still build and publish internal/private GitHub Releases
- the changelog and release package process still has value immediately
- the automated in-app release check becomes fully usable after the repository is public or after publishing a separate public `update.json` manifest
