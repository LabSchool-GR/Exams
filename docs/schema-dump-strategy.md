# Schema Dump Strategy

## Goal

Keep fresh installs fast and tidy without rewriting or pruning the historical migration files.

## Rules

1. Historical migrations remain in `database/migrations/`.
2. Committed schema dumps live in `database/schema/`.
3. We do not use `schema:dump --prune` in this repository.
4. The primary committed dump should target the real deployment engine, which is MySQL / MariaDB-compatible.
5. SQLite dumps are optional and only worth maintaining when the `sqlite3` CLI is available locally.

## Refresh Command

Use the repository wrapper command instead of calling Laravel's raw command directly:

```bash
php artisan app:schema-dump
```

Why this wrapper exists:

- it keeps the workflow explicit
- it never prunes migrations
- on Windows/XAMPP it prepends `C:\xampp\mysql\bin` to `PATH` so `mysqldump` / `mysql` can be found

To dump a specific connection:

```bash
php artisan app:schema-dump --database=mysql
```

## When To Refresh

Refresh the committed schema dump after merging schema-affecting migrations, especially when:

- tables are added or removed
- columns or indexes change
- foreign keys change
- the install path from scratch should pick up a cleaner baseline

## Safe Workflow

1. Make sure your target database is fully migrated.
2. Run `php artisan app:schema-dump --database=mysql`.
3. Commit the updated file under `database/schema/`.
4. Keep the migration files as-is for upgrade safety.

## Install Behavior

Laravel will automatically use the matching schema dump during `php artisan migrate` on a fresh database, then run only the migrations that are newer than the dump.
