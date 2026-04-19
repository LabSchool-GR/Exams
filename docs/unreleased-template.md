# Unreleased Template

Use this template when updating `CHANGELOG.md` during normal development or right before a release.

## Copy-Paste Block

```md
## Unreleased

### Added

- New feature for administrators/teachers/participants.

### Changed

- Existing behavior or UI changed in a meaningful way.

### Fixed

- Bug fix or regression fix.

### Security

- Security hardening, abuse-prevention, or privacy improvement.

### Upgrade Notes

- Manual step operators must know about, such as migrations, env changes, or cache refreshes.
```

## Writing Hints

- Keep bullets short and outcome-focused.
- Write what changed in the product, not just what changed in code.
- If a change affects deployment or operations, add it to `Upgrade Notes`.
- If a change is internal-only and invisible to users or operators, it usually does not belong in the changelog.

## Good Examples

### Added

- Update Center page for administrators with GitHub release visibility and package download links.

### Changed

- Student registration form now keeps the name field wide while pin and attempts fields share one row more compactly.

### Fixed

- Feedback submissions are now rate-limited to reduce spam risk on production deployments.

### Security

- Signed student links no longer depend on legacy persisted URL columns.

### Upgrade Notes

- After deploying this release, refresh configuration cache with `php artisan config:cache`.

## When the Repository Is Private

If the canonical GitHub repository is private, the in-app Update Center cannot fetch release metadata anonymously from the GitHub API.

That means:

- the release workflow can still create private GitHub Releases
- administrators with direct GitHub access can still download release packages there
- the public in-app Update Center release check will not work reliably until the repository becomes public or a separate authenticated/public update manifest is introduced

For now, while the repository remains private, continue maintaining `CHANGELOG.md` normally so the release process is ready when the repository is opened or when a manifest endpoint is added.
