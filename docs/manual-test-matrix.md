# Manual Test Matrix

## Scope

This checklist covers the current quiz attempt flow:

- persisted attempt lifecycle
- timer expiry
- resume
- no answer changes on previous questions
- skipped questions revisited only at the end
- dashboard read-only behavior for attempts

## Recommended Test Fixture

Create one quiz with these settings:

- `10` questions
- `allow_resume = true`
- `has_timer = true`
- short timer for timer tests, e.g. `120` seconds
- at least one registered student with a valid access flow

Use one browser profile for the examinee and one separate authenticated browser profile for the quiz creator.

## Cases

### 1. Normal flow without skip

Preconditions:

- New attempt
- No skipped questions

Steps:

1. Start the quiz.
2. Answer questions `1` to `10` in order.
3. Submit the final question.

Expected:

- Each next question opens in sequence.
- The examinee cannot reopen a previous answered question by URL.
- The final answer is saved before the attempt is finalized.
- The attempt ends with `status = submitted`.
- Score is calculated from all `10` questions.

### 2. Skip middle questions

Preconditions:

- New attempt

Steps:

1. Answer question `1`.
2. Skip questions `2`, `4`, and `6`.
3. Answer the remaining first-pass questions.
4. Observe the review phase.
5. Answer the skipped questions.

Expected:

- The skipped questions do not reappear during the first pass.
- After the first pass ends, only skipped questions appear.
- Already answered questions never reappear.
- During the review phase there is no skip button.
- After the last skipped question is answered, the quiz can be finalized normally.

### 3. Skip the last question of the first pass

Preconditions:

- New attempt
- At least one earlier skipped question

Steps:

1. Skip one middle question, e.g. question `3`.
2. Continue until question `10`.
3. Skip question `10`.

Expected:

- The first pass ends cleanly.
- The review phase starts with the skipped queue only.
- The examinee sees question `3` and question `10` in the review phase.
- No previously answered question becomes editable again.

### 4. Timer expiry during review pass

Preconditions:

- New timed attempt
- Reach the skipped-question review phase

Steps:

1. Skip several questions to force a review phase.
2. Stay on a skipped question until the timer expires.
3. Do not manually submit.

Expected:

- The countdown reaches zero.
- The attempt is finalized as expired.
- No further answers can be saved after expiry.
- Reopening the attempt leads to the end/result flow, not back into the question flow.

### 5. Resume in the middle of the review pass

Preconditions:

- `allow_resume = true`
- Timed attempt still within the allowed time
- Review phase already started

Steps:

1. Skip a few questions and reach the review phase.
2. Leave the attempt.
3. Re-enter using the same student flow before the timer expires.

Expected:

- The attempt resumes on the current skipped question, not from question `1`.
- The timer continues from the persisted deadline.
- Previously answered questions remain locked.
- Remaining skipped questions continue in order until completion.

### 6. Dashboard is read-only for attempts

Preconditions:

- Existing in-progress or completed attempt
- Quiz creator is logged in

Steps:

1. Open the quiz attempts dashboard.
2. Try any available submit/finalize action from the dashboard.

Expected:

- The dashboard does not alter attempt answers.
- Any direct submit action is rejected as read-only.
- Results remain viewable/exportable only.

### 7. Force-submit unload is session-bound

Preconditions:

- Non-resumable attempt
- Attempt is in progress

Steps:

1. Start the quiz as the examinee.
2. Close or reload the page to trigger unload behavior.
3. In a separate unrelated browser session, try to hit the force-submit endpoint for the same attempt.

Expected:

- Only the active exam session can auto-submit that attempt.
- An unrelated session cannot finalize the attempt.

## Quick Regression Checklist

Run these after any change to attempt flow:

1. Start attempt from scratch.
2. Answer one question and verify previous URL redirects forward.
3. Skip one question and verify it appears only in review pass.
4. Resume during first pass.
5. Resume during review pass.
6. Let a timed attempt expire.
7. Verify dashboard cannot mutate attempt state.
