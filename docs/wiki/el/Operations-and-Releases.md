# Λειτουργία και Εκδόσεις

Αυτή η σελίδα συνοψίζει τον τρόπο λειτουργίας παραγωγής, deployment και release του έργου.

Βασικές αναφορές:

- Runbook: <https://github.com/LabSchool-GR/Exams/blob/main/docs/runbook.md>
- Release workflow: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-workflow.md>
- Release checklist: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-checklist.md>

## Βασικός έλεγχος deployment

1. Ενεργοποιήστε maintenance mode αν χρειάζεται.
2. Κάντε pull ή ανεβάστε το σωστό release.
3. Εγκαταστήστε Composer dependencies.
4. Κάντε build τα frontend assets.
5. Τρέξτε migrations με `--force`.
6. Ανανεώστε τα caches.
7. Επιβεβαιώστε ότι scheduler και queue worker λειτουργούν.
8. Κάντε smoke test σε join, login, quiz flow και βασικά exports.

## Απαιτήσεις λειτουργίας

- writable `storage/` και `bootstrap/cache/`
- HTTPS σε production
- σωστή mail ρύθμιση για ειδοποιήσεις
- scheduler κάθε λεπτό
- queue worker για queued mail delivery
- `APP_SOURCE_URL` ρυθμισμένο στο σωστό source URL

## Μοντέλο releases

Το repository χρησιμοποιεί tag-based GitHub Actions release workflow.

Τυπική διαδικασία:

1. Βεβαιωθείτε ότι το `main` είναι πράσινο στο CI.
2. Δημιουργήστε semantic version tag όπως `v1.2.0`.
3. Κάντε push το tag.
4. Αφήστε το GitHub Actions να χτίσει το release package και τα release notes.

## Τι περιμένουμε από το package

Τα release packages προορίζονται για εγκατάσταση μέσω του in-app Update Center και περιλαμβάνουν:

- application source code
- production `vendor/`
- built frontend assets
- schema dump και migrations
- top-level `VERSION` file
- generated update metadata

Σκόπιμα εξαιρούνται:

- `.git/`
- `.github/`
- `node_modules/`
- `tests/`

## Πρακτικές συμβουλές

- Χρησιμοποιείτε το `docs/runbook.md` ως το βασικό deployment checklist.
- Κρατήστε το `CHANGELOG.md` ενημερωμένο με καθαρές release notes.
- Μετά από αλλαγές σε templates ή CSP, κάντε πάντα smoke test και στις δημόσιες σελίδες.
