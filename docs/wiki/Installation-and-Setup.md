# Installation and Setup

This page is the short version of the project setup flow. For the full project overview, use the repository README.

Primary reference:

- <https://github.com/LabSchool-GR/Exams/blob/main/README.md>

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- MySQL or MariaDB

## Local Setup

```bash
composer install
npm install
cp .env.example .env
php artisan app:install
npm run build
php artisan serve
```

Then open:

- `http://127.0.0.1:8000`

## First-Time Notes

- Make sure `storage/` and `bootstrap/cache/` are writable.
- Confirm mail settings if you want to test feedback, registration, or quota-related flows.
- Build frontend assets after template or runtime JavaScript changes.
- If the UI looks broken after frontend edits, rebuild assets before debugging Blade files.

## Common Commands

```bash
php artisan test
php vendor/bin/pint
npm run build
```

## Useful Pointers

- Production runbook: <https://github.com/LabSchool-GR/Exams/blob/main/docs/runbook.md>
- Release workflow: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-workflow.md>
- Security and privacy pages in app:
  - `/about`
  - `/terms`
  - `/privacy`
