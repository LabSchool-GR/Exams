# Operations Runbook

## Deployment Checklist

1. Put the application in maintenance mode if needed.
2. Pull the target release.
3. Install Composer dependencies with optimized autoloading.
4. Build frontend assets.
5. Run database migrations with `--force`.
6. Refresh caches.
7. Confirm scheduler and queue worker are active.
8. Smoke-test quiz join, teacher login, PDF export, and student verification.

Example:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan user:ensure-initial-admin
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Required Runtime Conditions

- PHP 8.2+
- writable `storage/` and `bootstrap/cache/`
- working mail configuration for feedback, registration, and quota request flows
- HTTPS enabled in production
- `APP_SOURCE_URL` set to the canonical repository or release source URL
- scheduler running every minute
- queue worker running for queued mail delivery

## Scheduler Responsibilities

The scheduler currently drives:

- `quiz-attempts:expire`
- `privacy:prune-exam-personal-data`

If the scheduler stops, timed attempts may remain open longer than expected and privacy retention tasks will not run.

## Queue Responsibilities

Queued mail delivery currently covers:

- feedback notifications
- teacher registration alerts for administrators
- quota increase requests

If the queue worker stops, the related emails will remain pending in `jobs` until a worker resumes.

## Smoke Test After Deploy

1. Teacher can sign in.
2. Dashboard loads.
3. Quiz catalogue and join page load.
4. Student code validation works.
5. Printable PDF export works.
6. Attempt PDF export works.
7. Certificate verification page works.

## Incident Notes

### PDF generation failure

Check:

- storage permissions
- application locale files for corrupted text
- recent changes in PDF Blade views
- DomPDF package availability from Composer install

### Timed attempts not expiring

Check:

- server cron configuration
- `php artisan schedule:list`
- application timezone configuration
- whether a stale scheduler process is blocked

### Mail not being delivered

Check:

- `MAIL_*` environment values
- SMTP/network reachability
- spam filtering on recipient side
- server logs for transport errors

## Manual Regression Reference

Use [manual-test-matrix.md](manual-test-matrix.md) after changes to attempt lifecycle or student flows.
For template-specific regression checks, use [quiz-template-smoke-checklist.md](quiz-template-smoke-checklist.md).
## Production Header Checks

After deploy, confirm these response headers on an authenticated page and on a public quiz entry page:

- `Content-Security-Policy`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Permissions-Policy`
- `Cross-Origin-Opener-Policy: same-origin`

If the application is behind HTTPS, also confirm `Strict-Transport-Security` is present.

## Expanded Smoke Test

1. Teacher login works and the dashboard loads.
2. Toast notifications render without console errors.
3. Quiz create/edit/list pages load with built assets.
4. Student registration page can copy personal links and validates CSV import client-side.
5. Public guest link opens and can start a quiz.
6. Student code validation still works.
7. Printable PDF export works.
8. Attempt PDF export works.
9. Certificate verification page works.
10. Quota request email or log-mail flow is recorded as expected.
