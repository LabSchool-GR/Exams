# Upgrade Packages

EXAMS releases can ship two package types:

- a full package for fresh installs or full application replacement
- an incremental upgrade package for a supported previous version

The first supported incremental path is:

```text
v2.0.0 -> v2.1.0
```

## Package Names

For `v2.1.0`, the release workflow publishes:

```text
labschool-exams-v2.1.0-full.zip
labschool-exams-v2.1.0-full.zip.sha256
labschool-exams-v2.0.0-to-v2.1.0-upgrade.zip
labschool-exams-v2.0.0-to-v2.1.0-upgrade.zip.sha256
update.json
```

## Upgrade Package Contents

The incremental package contains:

- files added, copied, renamed, or modified between `v2.0.0` and `v2.1.0`
- built frontend assets under `public/build`
- a top-level `VERSION` file for the target version
- `upgrade-manifest.json`
- `deleted-files.txt`
- `UPGRADE.md`

It does not include local runtime data such as `.env`, `storage/`, or `public/storage`.
It also excludes repository-only automation and test files such as `.github/` and `tests/`.

## Operator Steps

1. Confirm the installed version is the package `from_version`.
2. Back up the database and application files.
3. Put the application in maintenance mode:

```bash
php artisan down
```

4. Extract the upgrade package over the existing installation without replacing `.env`, `storage/`, or `public/storage`.
5. Delete paths listed in `deleted-files.txt`, if any.
6. Run Composer only when `composer_install_required` is `true` in `upgrade-manifest.json`:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

7. Run migrations and refresh caches:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

8. Bring the application back online:

```bash
php artisan up
```

9. Smoke-test login, quiz management, student join, and PDF export.

## Full Package Fallback

If the installed version does not match an available incremental package, use the full package and follow the normal deployment runbook.
