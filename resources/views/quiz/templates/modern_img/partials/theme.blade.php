<style>
:root {
    --modern-canvas: #eef3f7;
    --modern-surface: #ffffff;
    --modern-surface-muted: #f7f9fb;
    --modern-ink: #182733;
    --modern-muted: #607180;
    --modern-accent: #176b70;
    --modern-accent-strong: #0f565b;
    --modern-accent-soft: #e8f4f3;
    --modern-border: #dce5e9;
    --modern-danger: #a43f52;
    --modern-warning: #8b6419;
    --modern-shadow: 0 22px 54px rgba(39, 59, 73, 0.12);
}

html {
    background: var(--modern-canvas);
}

body {
    min-height: 100dvh;
    color: var(--modern-ink);
    background-image:
        radial-gradient(circle at 8% 8%, rgba(23, 107, 112, 0.1), transparent 30rem),
        radial-gradient(circle at 92% 90%, rgba(91, 123, 145, 0.1), transparent 32rem),
        linear-gradient(145deg, rgba(238, 243, 247, 0.72), rgba(238, 243, 247, 0.88)),
        @if(isset($quiz) && $quiz->image)
            url('{{ asset('storage/' . $quiz->image) }}');
        @else
            url('{{ asset('storage/bg-quiz.jpg') }}');
        @endif
    background-color: var(--modern-canvas);
    background-position: left top, right bottom, center, center;
    background-repeat: no-repeat;
    background-size: auto, auto, cover, cover;
    background-attachment: fixed;
    font-family: Figtree, "Segoe UI", sans-serif;
}

.overlay {
    background: linear-gradient(145deg, rgba(238, 243, 247, 0.64), rgba(238, 243, 247, 0.86));
}

.screen-shell {
    padding: clamp(1rem, 2.5vw, 2.5rem) clamp(0.8rem, 2vw, 1.5rem);
}

.exam-card {
    max-width: 880px;
    border: 1px solid rgba(255, 255, 255, 0.92);
    border-radius: 1.4rem;
    background: rgba(255, 255, 255, 0.96);
    box-shadow: var(--modern-shadow), 0 0 0 1px rgba(38, 65, 82, 0.05);
    backdrop-filter: none;
}

.exam-card__inner {
    padding: clamp(1.15rem, 3vw, 2.4rem);
}

.eyebrow {
    width: fit-content;
    margin-inline: auto;
    border: 1px solid #cfe1e2;
    border-radius: 999px;
    color: var(--modern-accent-strong);
    background: var(--modern-accent-soft);
    box-shadow: none;
    letter-spacing: 0.08em;
}

.app-title,
.result-title,
.question-title {
    color: var(--modern-ink);
    letter-spacing: -0.025em;
}

.helper-line,
.ready-line,
.result-note,
.metric-detail,
.question-progress-text,
.quiz-context-title {
    color: var(--modern-muted);
}

.hero-media,
.intro-media,
.question-image-shell {
    border: 1px solid var(--modern-border);
    border-radius: 1rem;
    background: var(--modern-surface-muted);
    box-shadow: none;
}

.hero-media img,
.intro-media img,
.question-image {
    border-radius: 0.8rem;
}

.description-box,
.error-box,
.selection-state,
.metrics-panel {
    border: 1px solid var(--modern-border);
    border-radius: 1rem;
    background: var(--modern-surface-muted);
    box-shadow: none;
}

.description-box {
    color: var(--modern-muted);
}

.description-box__label,
.form-label,
#selected-answer {
    color: var(--modern-ink);
}

.input-shell {
    border-color: var(--modern-border);
    border-radius: 0.9rem;
    background: var(--modern-surface-muted);
    box-shadow: none;
}

.input-shell:focus-within {
    border-color: var(--modern-accent);
    box-shadow: 0 0 0 4px rgba(23, 107, 112, 0.13);
}

.input-shell .form-control,
.input-shell .btn {
    color: var(--modern-ink);
    background: transparent;
}

.btn-start,
.btn-submit,
.btn-action.btn-primary-soft {
    border: 1px solid var(--modern-accent);
    color: #ffffff;
    background: var(--modern-accent);
    box-shadow: 0 10px 22px rgba(23, 107, 112, 0.2);
}

.btn-start:hover,
.btn-start:focus,
.btn-submit:hover,
.btn-submit:focus,
.btn-action.btn-primary-soft:hover,
.btn-action.btn-primary-soft:focus {
    border-color: var(--modern-accent-strong);
    color: #ffffff;
    background: var(--modern-accent-strong);
    transform: translateY(-1px);
}

.btn-skip,
.btn-action.btn-outline-soft {
    border-color: var(--modern-border);
    color: var(--modern-ink);
    background: #ffffff;
    box-shadow: none;
}

.btn-skip:hover,
.btn-skip:focus,
.btn-action.btn-outline-soft:hover,
.btn-action.btn-outline-soft:focus {
    border-color: #b8c8cf;
    color: var(--modern-accent-strong);
    background: var(--modern-surface-muted);
}

.student-pill,
.identity-chip,
.status-pill {
    border-color: var(--modern-border);
    color: var(--modern-ink);
    background: var(--modern-surface-muted);
    box-shadow: none;
}

.identity-chip {
    display: flex;
    width: fit-content;
    margin: 0.65rem auto 0;
}

.countdown-orb {
    width: 124px;
    height: 124px;
    border: 1px solid rgba(23, 107, 112, 0.1);
    background:
        radial-gradient(circle at 50% 42%, rgba(255, 255, 255, 0.98), rgba(232, 244, 243, 0.76));
    box-shadow:
        0 18px 38px rgba(39, 59, 73, 0.13),
        0 0 0 8px rgba(232, 244, 243, 0.58);
    isolation: isolate;
}

.countdown-orb::before {
    inset: -12px;
    background: radial-gradient(circle, rgba(23, 107, 112, 0.1), transparent 68%);
}

.countdown-ring {
    inset: 7px;
    width: calc(100% - 14px);
    height: calc(100% - 14px);
}

.countdown-ring__track {
    stroke: #d7e5e7;
}

.countdown-ring__progress {
    stroke: var(--modern-accent);
    stroke-width: 5;
    filter: drop-shadow(0 2px 3px rgba(23, 107, 112, 0.18));
}

.countdown-core {
    position: relative;
    z-index: 1;
    width: 76px;
    height: 76px;
    place-content: center;
    border: 1px solid var(--modern-border);
    border-radius: 50%;
    background: var(--modern-surface);
    box-shadow:
        0 10px 24px rgba(39, 59, 73, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
}

.countdown-value {
    color: var(--modern-accent-strong);
    font-size: 2rem;
    line-height: 0.9;
    letter-spacing: -0.04em;
}

.countdown-unit {
    margin-top: 0.42rem;
    color: var(--modern-muted);
    font-size: 0.62rem;
    letter-spacing: 0.14em;
}

.modern-media-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.65rem;
    width: 100%;
    max-width: 46rem;
    margin: 0.9rem auto 0;
    padding: 0.8rem 0.25rem 0;
    border-top: 1px solid rgba(96, 113, 128, 0.14);
    color: var(--modern-muted);
    background: transparent;
    font-size: clamp(0.69rem, 1.15vw, 0.76rem);
    line-height: 1.45;
    text-align: center;
}

.modern-media-notice i {
    flex: 0 0 auto;
    color: var(--modern-accent);
    opacity: 0.62;
}

.modern-media-notice p {
    margin: 0;
}

.question-panel--headline {
    justify-content: flex-start;
}

.question-title {
    width: 100%;
    max-width: none;
    padding: clamp(1rem, 2vw, 1.35rem) clamp(1rem, 2.5vw, 1.6rem);
    border: 1px solid var(--modern-border);
    border-left: 4px solid var(--modern-accent);
    border-radius: 1rem;
    color: var(--modern-ink);
    background: #ffffff;
    box-shadow: none;
    text-align: left;
    line-height: 1.35;
}

.answer-list {
    gap: 0.72rem;
}

.answer-option {
    position: relative;
    min-height: 4.15rem;
    padding: 0.85rem 1rem;
    border: 1px solid var(--modern-border);
    border-left: 4px solid #b8c7ce;
    border-radius: 1rem;
    color: var(--modern-ink);
    background: #ffffff;
    box-shadow: 0 7px 18px rgba(39, 59, 73, 0.045);
    transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

.answer-option:hover {
    border-color: #9ab2b8;
    border-left-color: var(--modern-accent);
    background: #fbfdfd;
    box-shadow: 0 10px 24px rgba(39, 59, 73, 0.08);
    transform: translateY(-1px);
}

.answer-option:focus-within {
    border-color: var(--modern-accent);
    box-shadow: 0 0 0 4px rgba(23, 107, 112, 0.13);
    outline: none;
}

.answer-option:has(input:checked) {
    border-color: var(--modern-accent);
    border-left-color: var(--modern-accent);
    color: var(--modern-ink);
    background: var(--modern-accent-soft);
    box-shadow: 0 9px 22px rgba(23, 107, 112, 0.12);
}

.answer-input {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    clip-path: inset(50%);
    white-space: nowrap;
    border: 0;
}

.answer-copy {
    color: var(--modern-ink);
    font-size: clamp(0.98rem, 1.25vw, 1.08rem);
    line-height: 1.48;
    text-align: left;
    overflow-wrap: anywhere;
}

.answer-copy > .fw-semibold:first-child {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2rem;
    min-height: 2rem;
    margin-right: 0.7rem !important;
    border: 1px solid #c9d7dc;
    border-radius: 0.65rem;
    color: var(--modern-accent-strong);
    background: var(--modern-surface-muted);
    font-size: 0.88rem;
    line-height: 1;
    vertical-align: middle;
}

.answer-option:has(input:checked) .answer-copy > .fw-semibold:first-child {
    border-color: var(--modern-accent);
    color: #ffffff;
    background: var(--modern-accent);
}

.answer-option input:checked ~ .answer-copy {
    color: var(--modern-ink);
    font-weight: 600;
}

.image-answer-list--compact .answer-option {
    height: 100%;
}

.image-answer-list--compact .answer-copy {
    text-align: center;
}

.question-actions-row,
.actions-grid {
    gap: 0.75rem;
}

.metric-value {
    color: var(--modern-accent-strong);
}

.status-pill.pass {
    color: #286044;
    background: #edf7f1;
}

.status-pill.fail {
    color: var(--modern-danger);
    background: #fbf0f2;
}

.status-pill.learning {
    color: #345f7b;
    background: #edf5fa;
}

@if(($templateScreen ?? '') === 'question')
.exam-card {
    max-width: 1120px;
}
@elseif(($templateScreen ?? '') === 'result')
.exam-card {
    max-width: 920px;
}
@endif

@media (min-width: 1100px) {
    .image-answer-list--compact {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: stretch;
    }
}

@media (max-width: 767px) {
    body {
        background-attachment: scroll;
    }

    .exam-card {
        border-radius: 1.1rem;
    }

    .question-title {
        font-size: clamp(1.05rem, 4.8vw, 1.3rem);
    }

    .answer-option {
        min-height: 3.8rem;
        padding: 0.75rem 0.8rem;
    }

    .image-answer-list--compact .answer-copy {
        text-align: left;
    }

    .modern-media-notice {
        align-items: flex-start;
        margin-top: 0.7rem;
        padding-top: 0.7rem;
        font-size: 0.68rem;
        text-align: left;
    }
}

@media (prefers-reduced-motion: reduce) {
    .fade-in,
    .ready-line {
        opacity: 1;
        animation: none;
    }

    .answer-option,
    .btn-start,
    .btn-submit,
    .btn-skip,
    .btn-action {
        transition: none;
    }

    .countdown-ring__progress {
        transition: none;
    }
}
</style>
