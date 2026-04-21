# Εγκατάσταση και Ρύθμιση

Αυτή η σελίδα είναι η σύντομη εκδοχή της διαδικασίας εγκατάστασης. Για πληρέστερη εικόνα του έργου, δείτε το README του repository.

Βασική αναφορά:

- <https://github.com/LabSchool-GR/Exams/blob/main/README.md>

## Απαιτήσεις

- PHP 8.2 ή νεότερη
- Composer
- Node.js και npm
- MySQL ή MariaDB

## Τοπική εγκατάσταση

```bash
composer install
npm install
cp .env.example .env
php artisan app:install
npm run build
php artisan serve
```

Στη συνέχεια ανοίξτε:

- `http://127.0.0.1:8000`

## Σημειώσεις πρώτης εγκατάστασης

- Βεβαιωθείτε ότι τα `storage/` και `bootstrap/cache/` είναι writable.
- Ρυθμίστε σωστά το mail αν θέλετε να ελέγξετε feedback, registration ή quota flows.
- Κάντε build τα frontend assets μετά από αλλαγές σε templates ή σε runtime JavaScript.
- Αν το UI εμφανίζεται σπασμένο μετά από frontend αλλαγές, κάντε πρώτα νέο build πριν ψάξετε τα Blade αρχεία.

## Χρήσιμες εντολές

```bash
php artisan test
php vendor/bin/pint
npm run build
```

## Χρήσιμες αναφορές

- Runbook παραγωγής: <https://github.com/LabSchool-GR/Exams/blob/main/docs/runbook.md>
- Release workflow: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-workflow.md>
- Σελίδες ενημέρωσης στην εφαρμογή:
  - `/about`
  - `/terms`
  - `/privacy`
