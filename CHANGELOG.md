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

## [v2.2.1] - 2026-07-30

### Added

- Quiz owners can export question text, answer choices, and correct-answer positions as a UTF-8 CSV and import the same format directly into another quiz.

### Changed

- Question CSV imports now use streaming RFC-compatible parsing, support up to the teacher's remaining quota with a 500-question safety cap, and provide localized validation feedback.

### Fixed

- Question pages now keep the background veil visible from the first paint and use a shorter card entrance, preventing a flash of the unfiltered background between questions.

### Security

- Updated the locked frontend build dependencies within their compatible release ranges, resolving the current Axios, Vite, Rollup, PostCSS, form-data, Lodash, and related npm advisories.

## [v2.2.0] - 2026-07-28

### Added

- Added the `modern_img` participant template with calm image-first styling and adaptive one- or two-column answer cards based on answer length and screen size.
- Teachers can now use pre-registered bulk exam slots and the Public Anonymous Pool, subject to their configured participant limit per quiz.
- Public Anonymous Pool quizzes can now export a printable invitation PDF with quiz details, the shared signed link, and its QR Code.

### Changed

- Template visibility and user assignments are now enforced from the database, including for templates with built-in view files.
- Re-running the core template seeder no longer overwrites administrator-managed visibility settings.
- Image-capable templates now use a neutral shared view layer, removing the direct coupling between `default_img` and `modern_img`.
- Administrators can now identify each quiz creator directly in the shared quiz collection, while teachers continue to see only their own quizzes.
- Guest-access links now present the read-only URL and copy action as one responsive, accessible control.
- Pre-registered exam-slot PDFs now provide handwritten name lines, and the consolidated student register uses the modern printable layout.
- Pre-registered bulk-exam participants are now presented as numbered exam slots (for example, `Exam Slot 0001`), keeping name-to-slot matching outside the application and distinct from the public anonymous pool.
- Participant limits now apply consistently to named participants, pre-registered exam slots, and completed or active Public Anonymous Pool positions.
- The Public Anonymous Pool link, copy action, and invitation download now share one compact responsive row.
- The release workflow now builds the incremental upgrade package from `v2.1.4` to `v2.2.0`.

### Fixed

- Completed Public Anonymous Pool submissions are now persisted reliably even when a browser does not preserve the final-submit button action; genuinely abandoned sessions remain temporary.
- Certificates for anonymous participants now provide a blank handwritten name line while named-participant certificates remain unchanged.
- Result PDFs for anonymous participants now leave the participant-name field blank for handwritten completion.
- Participant access PDFs now show the QR access caption only beneath the QR Code, removing the duplicate heading text.
- Anonymous exam cards, participant registers, certificates, and result PDFs now use consistent printable layouts and blank handwritten name fields where appropriate.
- Public Anonymous Pool quota checks now reserve positions transactionally and prevent concurrent requests from exceeding the owner’s configured limit.

### Security

- Template visibility and assignment are enforced server-side, preventing unassigned teachers from viewing or saving restricted templates.
- Bulk-exam and Public Anonymous Pool actions enforce quiz ownership, administrator privileges where required, signed-link validation, and participant quotas on the server.
- The Public Anonymous Pool invitation PDF is available only to the quiz owner or an administrator and reuses the exact signed public URL shown in the interface.
- Updated Dompdf to `v3.1.6`, resolving the known SVG, bitmap resource-exhaustion, local-file disclosure, and chroot-bypass advisories affecting earlier releases.

### Removed

- Removed the retired `retroAXD3_img` template. Existing quizzes using it are migrated to `default_img`.
- Removed the PayPal sponsorship page and its navigation links from the public documentation.

### Upgrade Notes

- Run `php artisan migrate` so quizzes that still reference `retroAXD3_img` are moved safely to `default_img`.
- Run `php artisan optimize:clear` after deploying the updated application files.

## [v2.1.4] - 2026-07-06

### Changed

- The release workflow now builds the incremental upgrade package from `v2.1.3` to `v2.1.4`.
- Upgrade manifests now derive their migration requirement from the actual release diff.

### Security

- Update announcement links now accept and render only browser-safe HTTP and HTTPS URLs, including for legacy records.
- Updated Laravel, Guzzle, PhpSpreadsheet, and Symfony dependencies to patched releases with no known Composer security advisories.

### Upgrade Notes

- No database migrations are required for this release.
- Run `php artisan optimize:clear` after deploying the updated application files.

## [v2.1.3] - 2026-05-27

### Changed

- Teacher self-registration now creates a pending registration first and creates the account only after the email confirmation link is opened.
- Administrator-created accounts still create users immediately and clean up any stale pending registration for the same email address.
- The release workflow now builds the incremental upgrade package from `v2.1.2` to `v2.1.3`.

### Security

- Pending registration tokens are stored only as hashes and expire after 24 hours by default.
- Registration confirmation email delivery is rate limited per recipient email address.
- Failed registration confirmation email delivery deletes the pending registration instead of leaving unused data behind.

### Upgrade Notes

- Run `php artisan migrate` to create the `pending_registrations` table.
- Run `php artisan optimize:clear` after deploying the new release files.

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
