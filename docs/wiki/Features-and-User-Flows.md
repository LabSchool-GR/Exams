# Features and User Flows

The platform supports both controlled educational use cases and more open public quiz experiences.

## Main Roles

- administrators
- teachers or quiz creators
- registered students
- guests or public participants

## Core Capabilities

- create quizzes with single or multiple correct answers
- organize quizzes into categories and collections
- import questions and participants from CSV
- support guest participation with controlled public links
- expose a public catalogue when enabled
- export reports and analytics
- generate success certificates with verification support
- enforce quotas, limits, and approval workflows

## Typical User Flows

### Teacher Flow

1. Sign in.
2. Create or edit a quiz.
3. Add questions, answers, timing, and visibility settings.
4. Choose a quiz template when visual style matters.
5. Publish and distribute the quiz.

### Student Flow

1. Open a join or access page.
2. Enter code or use the provided participation link.
3. Start the quiz and progress through the question flow.
4. View results, retry if allowed, or download a certificate when eligible.

### Guest Flow

1. Open the public quiz page.
2. Start directly or enter the required access code.
3. Complete the quiz.
4. Reach the result screen with the template-specific experience.

## Template System

Quiz presentation is template-driven. The project includes multiple template families for different branding and event needs.

Examples include:

- `default`
- `default_img`
- `newdefault`
- `alexpolis_img`
- `exakoustou_img`
- `retropc_img`
- `uni_img`
- `retroAXD3_img`

Template work typically affects:

- start screen
- student join screen
- question screen
- result screen
- shared theme partials

When changing templates, remember to check responsive behavior, countdown logic, CSP compliance, and quiz runtime interactions.
