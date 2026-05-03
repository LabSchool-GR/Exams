# Changelog

All notable changes to this project should be documented in this file.

The format is intentionally lightweight and release-friendly so entries can be reused in:

- GitHub Releases
- the in-app Update Center
- deployment notes for operators

## Writing Policy

- Keep entries short and user-relevant.
- Group changes by impact, not by commit order.
- Prefer plain language over internal implementation detail.
- Mention migrations, install/deploy steps, and breaking behavior explicitly.
- Keep sensitive/internal-only notes out of the changelog.

## Recommended Sections

- `Added` for new capabilities
- `Changed` for behavior or UX changes
- `Fixed` for bug fixes and regressions
- `Security` for hardening and operational safeguards
- `Upgrade Notes` for anything operators must do manually

## Unreleased

## [v2.1.0] - 2026-05-03

### Added

- In-app Update Center for administrators with GitHub release checks and downloadable update packages.
- Teachers can copy an existing quiz into a fresh draft with a new quiz code, preserving settings, questions, and answers while leaving participants and results behind.
- Release workflow now builds both a full package and a `v2.0.0` to `v2.1.0` incremental upgrade package.

### Changed

- Release packaging now includes a `VERSION` file so zip-based installs can display the correct application version.

## [v1.0.0] - 2026-04-19

### Added

- Initial public release workflow with GitHub release packages, checksums, and downloadable assets.
- Read-only Update Center that shows the installed version, latest GitHub release, changelog, and package download link.

### Security

- Release packages exclude local environment files and transient runtime artifacts.

### Upgrade Notes

- Operators using release zip packages can rely on the packaged `VERSION` file when `APP_VERSION` is not explicitly set.
