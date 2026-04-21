# Δοκιμές και Πρότυπα Quiz

Το έργο συνδυάζει backend λογική, Blade templates, quiz runtime JavaScript και δημόσια theme variants. Για αυτό, οι έλεγχοι που λαμβάνουν υπόψη τα templates είναι απαραίτητοι.

Βασικές αναφορές:

- Manual test matrix: <https://github.com/LabSchool-GR/Exams/blob/main/docs/manual-test-matrix.md>
- Checklist ελέγχου templates: <https://github.com/LabSchool-GR/Exams/blob/main/docs/quiz-template-smoke-checklist.md>

## Αυτοματοποιημένοι έλεγχοι

Προτεινόμενη τοπική βάση ελέγχου:

```bash
php vendor/bin/pint
php artisan test
npm run build
```

Οι έλεγχοι αυτοί καλύπτουν formatting, συμπεριφορά εφαρμογής και ακεραιότητα frontend assets.

## Χειροκίνητοι έλεγχοι που έχουν τη μεγαλύτερη αξία

- η join page φορτώνει σωστά
- το countdown της start screen ενημερώνεται σωστά
- η πλοήγηση στις ερωτήσεις λειτουργεί κανονικά
- η result page εμφανίζεται χωρίς runtime errors
- το timer, το skip και το resume συμπεριφέρονται σωστά
- τα certificates και τα exports εμφανίζονται μόνο όπου επιτρέπεται

## Συνηθισμένοι κίνδυνοι στα templates

Οι αλλαγές σε templates συχνά δημιουργούν προβλήματα σε σημεία όπως:

- διπλά labels ή διπλή αρίθμηση
- μίξη ελληνικών και αγγλικών labels λόγω locale mismatch
- κακή responsive συμπεριφορά σε κινητό ή tablet
- CSP violations από inline scripts
- transitions που επηρεάζουν submit ή navigation
- selected-answer styling που ξεχωρίζει μόνο σε ένα χρώμα

## Για δημόσια event templates

Event templates όπως το `retroAXD3_img` χρειάζονται και επιπλέον έλεγχο σε:

- social preview metadata
- ποιότητα εμφάνισης όταν μοιράζονται δημόσια
- guest-page access flows
- οπτική συνοχή μεταξύ start, student, question και result screens
- branded wording που δεν θυμίζει εξετάσεις όταν το quiz είναι καθαρά ψυχαγωγικό

## Πρακτικός κανόνας

Αν πειράξετε κάποιο από τα παρακάτω, κάντε και automated και manual checks:

- `resources/views/quiz/templates/**`
- `resources/views/layouts/quiz_guest.blade.php`
- `resources/js/app.js`
- CSP ή response-header logic
