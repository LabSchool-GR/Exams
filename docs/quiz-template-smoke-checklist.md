# Quiz Template Smoke Checklist

Use this checklist after template, CSP, or runtime-JS changes.

## Template Families

- `default`
- `default_img`
- `newdefault`
- `alexpolis_img`
- `exakoustou_img`
- `retropc_img`
- `uni_img`

## Entry Flow Per Template

For each template family:

1. Open the student join page and confirm the layout renders without console errors.
2. Confirm the student code field receives focus automatically.
3. Toggle the student code visibility button and verify the eye icon changes state.
4. Submit invalid data once and confirm validation feedback still appears.

## Start Screen Per Template

For each template family:

1. Start a valid attempt.
2. Confirm the countdown text updates every second.
3. Confirm the automatic redirect starts the first question when the countdown reaches zero.

## Question Screen Per Template

For each template family:

1. Open a single-answer question and verify the submit button enables only after one selection.
2. Open a multi-answer question and verify the submit button enables only when the required number of answers is selected.
3. If the quiz has a timer, confirm the remaining time updates and the progress bar shrinks.
4. If the question has an image, confirm the image renders and keeps its layout.
5. On a non-final question, use skip once and confirm navigation continues normally.
6. On the final question, submit and confirm the completion state renders without JavaScript errors.

## Result Screen Per Template

For each template family:

1. Confirm the score summary renders.
2. Confirm the PDF button appears only when the attempt is eligible.
3. Confirm retry logic matches the quiz rules for guest and registered students.

## Cross-Cutting Checks

- Confirm no page uses inline `<script>` tags or inline event attributes.
- Confirm the response header is `Content-Security-Policy`, not report-only.
- Confirm the built asset bundle loads on authenticated pages and public quiz pages.
- Confirm Greek copy renders correctly, especially in `uni_img` headers.
