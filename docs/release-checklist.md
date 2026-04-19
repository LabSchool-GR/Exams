# Release Checklist

Use this checklist before creating and pushing a new Git tag such as `v1.2.0`.

## 1. Confirm the release scope

- Verify which fixes, features, and documentation updates are intended for the release.
- Confirm that no unfinished or experimental work is accidentally included.
- Make sure the target branch is the correct release branch, usually `main`.

## 2. Update version-facing metadata

- Decide the new semantic version tag (`vMAJOR.MINOR.PATCH`).
- If the deployment will use `.env`-based versioning, prepare the corresponding `APP_VERSION` update for operators.
- Review user-facing release text, especially if the update should be highlighted in the in-app Update Center.
- Move the relevant `Unreleased` notes into a matching version section in `CHANGELOG.md`.

## 3. Refresh database and install artifacts if needed

- If schema changes were made, confirm migrations are final and reversible enough for deployment expectations.
- If the fresh-install baseline changed, regenerate the schema dump:

```bash
php artisan app:schema-dump --database=mysql
```

- Check whether seeders, installer flow, or operational docs need updates.

## 4. Run verification locally

- Run the full automated test suite:

```bash
php artisan test
```

- Build frontend assets:

```bash
npm run build
```

- If relevant, also run:

```bash
composer validate --strict
```

## 5. Perform a quick functional smoke check

- Teacher login works.
- Dashboard loads.
- Quiz create/edit/list pages load.
- Student join flow still works.
- Registered-student management still works.
- PDF export still works.
- Any feature touched by the release is manually checked once.

Use these references when needed:

- [docs/runbook.md](runbook.md)
- [docs/manual-test-matrix.md](manual-test-matrix.md)
- [docs/quiz-template-smoke-checklist.md](quiz-template-smoke-checklist.md)

## 6. Review release packaging readiness

- Confirm `.env` is not part of tracked changes.
- Confirm local-only artifacts are not accidentally staged.
- Confirm the release package should include the latest built assets and production dependencies through the GitHub workflow.
- Confirm `APP_SOURCE_URL` still points to the canonical repository.

## 7. Review git state

- Check the working tree:

```bash
git status
```

- Make sure only intended files are committed.
- Ensure the commit history is in a state you are comfortable publishing.

## 8. Create and publish the tag

```bash
git tag v1.2.0
git push origin v1.2.0
```

This triggers the GitHub release workflow in [.github/workflows/release.yml](../.github/workflows/release.yml).

## 9. Post-release verification

- Confirm the GitHub Actions release workflow succeeded.
- Confirm the GitHub Release contains:
  - the curated release notes from `CHANGELOG.md` or the expected fallback text
  - the `.zip` package
  - the `.sha256` checksum
- Confirm the in-app Update Center can see the new version.
- Confirm the release notes/changelog render acceptably in the Update Center.

## 10. Deployment follow-through

- If this release is intended for a live environment, follow the deployment steps in [docs/runbook.md](runbook.md).
- After deploy, confirm the installed instance reports the expected version in the footer and Update Center.
