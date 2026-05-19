# Exams Wiki

Welcome to the `Exams` project wiki.

`Exams` is a Laravel-based quiz and assessment platform for schools, training providers, and public quiz experiences. The application supports teacher-managed quiz authoring, student participation flows, guest access, analytics, exports, certificates, and multiple visual quiz templates.

This wiki is meant to provide a fast operational overview. Detailed technical and release documentation remains inside the repository so it can evolve together with the code.

## What You Can Find Here

- installation and local setup guidance
- feature and user-flow overview
- operations and deployment notes
- testing and template QA guidance
- security, privacy, and compliance references

## Recommended Reading Order

1. [Installation and Setup](Installation-and-Setup)
2. [Features and User Flows](Features-and-User-Flows)
3. [Operations and Releases](Operations-and-Releases)
4. [Testing and Quiz Templates](Testing-and-Quiz-Templates)
5. [Security Privacy and Compliance](Security-Privacy-and-Compliance)

## Canonical Project Docs

- README: <https://github.com/LabSchool-GR/Exams/blob/main/README.md>
- Operations runbook: <https://github.com/LabSchool-GR/Exams/blob/main/docs/runbook.md>
- Release workflow: <https://github.com/LabSchool-GR/Exams/blob/main/docs/release-workflow.md>
- Manual test matrix: <https://github.com/LabSchool-GR/Exams/blob/main/docs/manual-test-matrix.md>
- Template smoke checklist: <https://github.com/LabSchool-GR/Exams/blob/main/docs/quiz-template-smoke-checklist.md>

## Current Highlights

- Laravel 12 application with PHP 8.2+
- multiple public and authenticated quiz flows
- template-based quiz presentation with the default application templates
- GitHub Actions CI and tag-based release packaging
- privacy and security controls such as CSP, throttling, and data-pruning jobs
