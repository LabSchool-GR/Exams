# Security Privacy and Compliance

The project includes several safeguards for public and school-oriented quiz usage. This page is a quick index, not a replacement for the in-app legal pages or code-level controls.

## Areas Covered by the Platform

- security headers
- Content Security Policy
- rate limiting and request throttling
- signed URL access flows where applicable
- privacy-aware data retention
- operational controls for queued and scheduled jobs

## In-App Legal Pages

Public informational pages are available in the application:

- `/about`
- `/terms`
- `/privacy`

Relevant view files in the repository:

- <https://github.com/LabSchool-GR/Exams/blob/main/resources/views/about.blade.php>
- <https://github.com/LabSchool-GR/Exams/blob/main/resources/views/terms.blade.php>
- <https://github.com/LabSchool-GR/Exams/blob/main/resources/views/privacy.blade.php>

## Operational Security Notes

- Keep `APP_SOURCE_URL` configured so public source references remain accurate.
- Verify CSP stays enforced on public quiz pages after template edits.
- Avoid inline scripts in Blade templates when theme behavior can be moved into built assets.
- Confirm scheduler and queue workers are healthy, because some privacy and expiry flows depend on them.

## Privacy-Oriented Jobs

The scheduler currently drives privacy-sensitive background work, including personal-data pruning. If scheduled tasks stop running, privacy retention behavior may drift from policy expectations.

## Good Release Hygiene

- review public pages after UI changes
- confirm guest routes do not leak internal-only controls
- validate certificate and verification flows
- test exports and signed links after framework or dependency upgrades
