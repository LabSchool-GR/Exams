# Exams

Educational quiz and assessment platform built with Laravel for school and training use cases.

## License

This project is licensed under the GNU Affero General Public License, version 3 or any later version (`AGPL-3.0-or-later`).
If you deploy a modified version for users over a network, you must also make the corresponding source code available to those users under the same license.
See [LICENSE.md](LICENSE.md) for the full terms.

## Core Capabilities

- quiz creation and management
- question banks with multiple answers and CSV import
- registered-student flows with signed personal links
- public guest quiz flows with signed access links
- quiz attempt tracking, exports, PDFs, and certificates
- privacy-focused student handling with anonymization routines
- security controls such as rate limiting, signed URLs, and encrypted student names at rest

## Stack

- PHP 8.2+
- Laravel 12
- DomPDF for PDF generation
- Laravel Excel for XLSX exports
- Vite for frontend assets
- Pest / PHPUnit for automated tests

## Local Setup

1. Install PHP and Composer dependencies.
2. Install frontend dependencies.
3. Create your environment file and application key.
4. Run migrations.
5. Link public storage.
6. Start the application.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan user:ensure-initial-admin
php artisan storage:link
npm run build
php artisan serve
```

For local frontend development you can use:

```bash
npm run dev
```

## Testing

Automated test suite:

```bash
php artisan test
```

Manual regression checklist:

- [docs/manual-test-matrix.md](docs/manual-test-matrix.md)

A GitHub Actions workflow is included in [.github/workflows/tests.yml](.github/workflows/tests.yml) to run the test suite on pushes and pull requests.

## PDF Security Note

Embedded PHP execution in DomPDF templates is intentionally disabled.
Page footers are rendered from controller-side canvas callbacks instead of `script type="text/php"` blocks.

## Operations Summary

Production deployments should include:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_SOURCE_URL=https://github.com/your-org/your-repo`
- HTTPS at the reverse proxy or web server layer
- writable `storage/` and `bootstrap/cache/`
- `php artisan migrate --force`
- `php artisan user:ensure-initial-admin`
- `php artisan storage:link`
- `php artisan config:cache`
- `php artisan route:cache`
- `php artisan view:cache`
- a running queue worker for queued mail delivery
- a cron entry for Laravel scheduler

Scheduler entry:

```cron
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

See the full deployment and maintenance checklist in [docs/runbook.md](docs/runbook.md).

## Maintenance Commands

The scheduler currently runs these application-specific maintenance tasks:

- `php artisan quiz-attempts:expire`
- `php artisan privacy:prune-exam-personal-data`

## Security Status

Current hardening already includes:

- strict signed-link checks for public access flows
- brute-force throttling for quiz and student code entry
- conservative browser security headers
- privacy pruning and anonymization commands

The application now ships with an enforcing `Content-Security-Policy` header by default. If you need temporary rollout diagnostics, you can switch back to report-only mode with `SECURITY_CSP_REPORT_ONLY=true`.

## Open Source Compliance

For public deployments, set `APP_SOURCE_URL` to the canonical repository or source archive URL for the exact codebase you are running.
This makes it easier for users of the hosted instance to locate the corresponding source code, which is especially useful for AGPL compliance.
