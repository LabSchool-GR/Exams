# Testing and Quiz Templates

This project combines backend logic, Blade templates, quiz runtime JavaScript, and public-facing theme variants, so template-aware testing matters.

Primary references:

- Manual test matrix: <https://github.com/LabSchool-GR/Exams/blob/main/docs/manual-test-matrix.md>
- Template smoke checklist: <https://github.com/LabSchool-GR/Exams/blob/main/docs/quiz-template-smoke-checklist.md>

## Automated Checks

Recommended local baseline:

```bash
php vendor/bin/pint
php artisan test
npm run build
```

These checks cover formatting, application behavior, and frontend asset integrity.

## Manual Checks That Matter Most

- join page renders correctly
- start countdown updates correctly
- question navigation works
- result page renders without runtime errors
- timer, skip, and resume behavior remain correct
- certificate and export logic remain available only where allowed

## Template-Specific Risks

Template changes often introduce issues in these areas:

- duplicate labels or numbering
- locale mismatches between Greek and English copy
- broken responsive layout on mobile or tablet
- CSP violations from inline scripts
- transitions that interfere with submit or navigation behavior
- selected-answer styling that is visible for one color only

## For Custom Public Event Templates

Custom templates that are added locally beyond the default application templates should also be checked for:

- social preview metadata
- public-share presentation quality
- guest-page access flows
- visual consistency across start, student, question, and result screens
- branded copy that avoids exam-like wording where the quiz is purely recreational

## Practical Rule

If you touch any of the following, run both automated and manual checks:

- `resources/views/quiz/templates/**`
- `resources/views/layouts/quiz_guest.blade.php`
- `resources/js/app.js`
- CSP or response-header logic
