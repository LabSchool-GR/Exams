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

## [v2.1.2] - 2026-05-20

### Added

- GitHub Pages documentation pages now include canonical URLs, Open Graph metadata, Twitter card metadata, and a shared social preview image.
- Application layouts now share a reusable SEO metadata partial for consistent title, description, canonical, Open Graph, and Twitter card output.

### Changed

- Public quiz and participant-facing pages now emit fuller social preview metadata while preserving quiz-specific titles, descriptions, and images.
- The release workflow now builds the incremental upgrade package from `v2.1.1` to `v2.1.2`.

### Fixed

- Greek fallback text for public quiz social previews now renders correctly when a quiz has no custom description.

### Upgrade Notes

- No database migrations are required for this release.

## [v2.1.1] - 2026-05-19

### Added

- GitHub Pages documentation site with installation, upgrade, security, user-guide, and support pages.
- Public quiz links can now be configured as non-expiring signed links by setting `SECURITY_PUBLIC_LINK_TTL_MINUTES=0`.

### Changed

- README now points readers to the published documentation pages and uses the compact project logo.
- Dashboard teacher guide link now opens the online user guide instead of the legacy PDF.
- Teacher navigation no longer shows the application update center to non-admin accounts.
- TV Mode typography is smaller and more readable on classroom displays.
- Participant join page no longer shows a source-code link inside the entry card.

### Fixed

- Account deletion confirmation modal now opens in the correct viewport layer and remains usable on small screens.
- Password reset emails now include the missing Greek and English body lines.
- Documentation pages with command examples now adapt better to small mobile screens.

### Security

- Public quiz URLs can remain signed without an expiry timestamp when explicitly configured for permanent guest access.

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
