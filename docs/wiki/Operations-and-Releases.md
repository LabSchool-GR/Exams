# Operations and Releases

This page summarizes how to deploy, verify, and release the application.

Primary references:

- Runbook: <https://github.com/LabSchool-GR/Exams/blob/main/docs/runbook.md>
- Release workflow: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-workflow.md>
- Release checklist: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-checklist.md>

## Deployment Checklist

1. Put the application in maintenance mode if needed.
2. Pull or unpack the target release.
3. Install Composer dependencies.
4. Build frontend assets.
5. Run migrations with `--force`.
6. Refresh caches.
7. Confirm scheduler and queue worker health.
8. Smoke-test join, login, quiz flow, and key exports.

## Runtime Requirements

- writable `storage/` and `bootstrap/cache/`
- HTTPS in production
- mail configuration for platform notifications
- scheduler running every minute
- queue worker for queued mail delivery
- `APP_SOURCE_URL` set to the canonical project or release source

## Release Model

The repository uses a tag-based GitHub Actions release workflow.

Typical flow:

1. Ensure `main` is green in CI.
2. Create a semantic version tag such as `v1.2.0`.
3. Push the tag.
4. Let GitHub Actions build the release package and notes.

## Package Expectations

Release packages are designed for administrator installation through the in-app Update Center and include:

- application source
- production `vendor/`
- built frontend assets
- schema dump and migrations
- top-level `VERSION` file
- generated update metadata

They intentionally exclude development and CI material such as:

- `.git/`
- `.github/`
- `node_modules/`
- `tests/`

## Operational Advice

- Treat `docs/runbook.md` as the authoritative deployment checklist.
- Keep release notes curated in `CHANGELOG.md`.
- After any template or CSP-related change, include a public-page smoke test before release.
