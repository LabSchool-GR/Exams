<style>
:root {
    --retro-bg: #070b18;
    --retro-bg-2: #101733;
    --retro-shell: #d5cec0;
    --retro-shell-shadow: #918775;
    --retro-screen: #0b111f;
    --retro-screen-glow: rgba(69, 255, 240, 0.12);
    --retro-cyan: #59f2ff;
    --retro-pink: #ff4fcf;
    --retro-purple: #8d72ff;
    --retro-gold: #ffd45f;
    --retro-green: #78ff8f;
    --retro-red: #ff646a;
    --retro-text: #f6f0df;
    --retro-muted: #b7bfd3;
    --retro-black: #05070d;
    --retro-radius: 26px;
    --retro-stage-min-height: 470px;
    --retro-stage-min-height-mobile: 420px;
}

body.quiz-participant-shell {
    min-height: 100dvh;
    color: var(--retro-text);
    background:
        radial-gradient(circle at top, rgba(141, 114, 255, 0.24), transparent 28%),
        radial-gradient(circle at bottom, rgba(255, 79, 207, 0.16), transparent 24%),
        linear-gradient(180deg, rgba(7, 12, 24, 0.56), rgba(5, 9, 18, 0.74)),
        @if(isset($quiz) && $quiz->image)
            url('{{ asset('storage/' . $quiz->image) }}');
        @else
            url('{{ asset('storage/bg-quiz.jpg') }}');
        @endif
    background-size: cover;
    background-position: center top;
    background-attachment: fixed;
    font-family: "VT323", "IBM Plex Mono", monospace;
}

.quiz-participant-main {
    position: relative;
    overflow: hidden;
}

.retro-page {
    min-height: 100dvh;
    padding: clamp(1rem, 2vw, 1.75rem);
    display: flex;
    align-items: center;
    justify-content: center;
}

.retro-frame {
    width: min(100%, 1180px);
}

.retro-header {
    margin-bottom: 1rem;
    text-align: center;
}

.retro-title {
    margin: 0;
    font-family: "Noto Sans Display", sans-serif;
    font-size: clamp(2rem, 3.8vw, 3.6rem);
    line-height: 1;
    font-weight: 900;
    letter-spacing: 0.04em;
    color: #ffe58d;
    text-shadow:
        0 0 6px rgba(255, 229, 141, 0.85),
        0 0 20px rgba(255, 79, 207, 0.5),
        0 0 36px rgba(89, 242, 255, 0.28);
}

.retro-subtitle {
    margin: 0.45rem 0 0;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.92rem, 1.6vw, 1.15rem);
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--retro-cyan);
}

.retro-monitor {
    padding: clamp(1rem, 1.8vw, 1.45rem);
    border-radius: calc(var(--retro-radius) + 8px);
    background: linear-gradient(145deg, #ece7dc, #c9bfaf);
    border: 1px solid rgba(95, 82, 56, 0.2);
    box-shadow:
        inset 0 2px 0 rgba(255, 255, 255, 0.72),
        inset 0 -10px 18px rgba(97, 81, 52, 0.24),
        0 24px 50px rgba(0, 0, 0, 0.45);
}

.retro-screen {
    position: relative;
    min-height: 360px;
    padding: clamp(1rem, 2vw, 1.4rem);
    border-radius: var(--retro-radius);
    background:
        radial-gradient(circle at center, rgba(89, 242, 255, 0.04), transparent 35%),
        linear-gradient(180deg, #0d1119, #070a11);
    border: 6px solid #1a1d22;
    box-shadow:
        inset 0 0 24px rgba(89, 242, 255, 0.06),
        inset 0 -18px 34px rgba(0, 0, 0, 0.54),
        0 0 28px rgba(89, 242, 255, 0.06);
    overflow: hidden;
}

.retro-screen::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.22) 50%),
        repeating-linear-gradient(
            90deg,
            rgba(255, 210, 80, 0.04) 0,
            rgba(255, 210, 80, 0.04) 1px,
            transparent 1px,
            transparent 52px
        );
    background-size: 100% 4px, 53px 100%;
    pointer-events: none;
    opacity: 0.75;
}

.retro-screen::after {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: inherit;
    box-shadow: inset 0 0 54px rgba(0, 0, 0, 0.82);
    pointer-events: none;
}

.retro-screen__inner {
    position: relative;
    z-index: 1;
    height: 100%;
}

.retro-screen__inner > * {
    opacity: 0;
    animation: retroElementFadeIn 0.42s ease-out forwards;
}

.retro-screen__inner > :nth-child(1) {
    animation-delay: 0.04s;
}

.retro-screen__inner > :nth-child(2) {
    animation-delay: 0.1s;
}

.retro-screen__inner > :nth-child(3) {
    animation-delay: 0.16s;
}

.retro-screen__inner > :nth-child(4) {
    animation-delay: 0.22s;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > * {
    opacity: 0;
    animation: retroElementFadeIn 0.5s ease-out forwards;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > :nth-child(1) {
    animation-delay: 0.12s;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > :nth-child(2) {
    animation-delay: 0.18s;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > :nth-child(3) {
    animation-delay: 0.24s;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > :nth-child(4) {
    animation-delay: 0.3s;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > :nth-child(5) {
    animation-delay: 0.36s;
}

:where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > :nth-child(6) {
    animation-delay: 0.42s;
}

.retro-screen--exiting .retro-screen__inner > *,
.retro-screen--exiting :where(
    .retro-start-screen,
    .retro-student-shell,
    .retro-result-screen,
    .retro-question-screen,
    .retro-learning-panel,
    .retro-no-access,
    .retro-form-stack,
    .retro-answer-grid
) > * {
    animation: retroElementFadeOut 0.18s ease-in forwards !important;
}

@keyframes retroElementFadeIn {
    from {
        opacity: 0;
        transform: translateY(10px) scale(0.985);
        filter: blur(2px);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
    }
}

@keyframes retroElementFadeOut {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
        filter: blur(0);
    }
    to {
        opacity: 0;
        transform: translateY(-6px) scale(0.992);
        filter: blur(1.6px);
    }
}

.retro-screen-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.8rem;
    margin-bottom: 1rem;
    color: var(--retro-green);
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.78rem, 1.2vw, 0.95rem);
    letter-spacing: 0.18em;
    text-transform: uppercase;
    text-shadow: 0 0 8px rgba(120, 255, 143, 0.25);
}

.retro-screen-bar__label {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.retro-screen-bar__label::before {
    content: "";
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 8px currentColor;
    animation: retroBlink 1.1s steps(1, end) infinite;
}

@keyframes retroBlink {
    50% {
        opacity: 0.35;
    }
}

.retro-screen-meta {
    color: var(--retro-muted);
    text-shadow: none;
}

.retro-screen-heading {
    display: grid;
    gap: 0.3rem;
    margin-bottom: 0.35rem;
}

.retro-screen-heading__title {
    margin: 0;
    color: #ffe58d;
    font-family: "Noto Sans Display", sans-serif;
    font-size: clamp(1.25rem, 2.2vw, 2rem);
    line-height: 1.02;
    font-weight: 900;
    text-align: center;
    text-shadow:
        0 0 6px rgba(255, 229, 141, 0.55),
        0 0 18px rgba(255, 79, 207, 0.22);
}

.retro-screen-heading__subtitle {
    margin: 0;
    color: var(--retro-cyan);
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.78rem, 1vw, 0.92rem);
    text-align: center;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.retro-led {
    color: var(--retro-red);
    font-family: "IBM Plex Mono", monospace;
    letter-spacing: 0.12em;
    text-shadow: 0 0 10px rgba(255, 100, 106, 0.35);
}

.retro-screen-copy {
    color: var(--retro-text);
    font-size: clamp(1.12rem, 1.8vw, 1.38rem);
    line-height: 1.45;
}

.retro-display-title {
    margin: 0 0 1rem;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.82rem, 1.2vw, 0.95rem);
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--retro-cyan);
}

.retro-status-line {
    margin-top: 1rem;
    color: var(--retro-muted);
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.78rem, 1.05vw, 0.9rem);
    letter-spacing: 0.08em;
}

.retro-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    min-height: 54px;
    padding: 0.8rem 1rem;
    border-radius: 14px;
    border: 3px solid var(--retro-black);
    background: linear-gradient(180deg, #181f32, #0e1423);
    color: var(--retro-text);
    font-family: "IBM Plex Mono", monospace;
    font-size: 0.92rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    text-decoration: none;
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.12),
        0 6px 0 rgba(0, 0, 0, 0.75);
    transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
}

.retro-action:hover,
.retro-action:focus {
    transform: translateY(1px);
    box-shadow:
        inset 0 1px 0 rgba(255, 255, 255, 0.12),
        0 4px 0 rgba(0, 0, 0, 0.75);
    filter: brightness(1.05);
    text-decoration: none;
}

.retro-action--primary {
    background: linear-gradient(180deg, #2ee3ff, #1a8cff);
    color: #06111a;
}

.retro-action--secondary {
    background: linear-gradient(180deg, #ff6bd7, #c147b4);
}

.retro-action[disabled],
.retro-action:disabled {
    opacity: 0.55;
    cursor: not-allowed;
    transform: none;
    filter: none;
}

.retro-panel {
    padding: 1rem;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.retro-metric-value {
    font-family: "Noto Sans Display", sans-serif;
    font-size: clamp(2.2rem, 4.4vw, 4rem);
    font-weight: 900;
    line-height: 1;
    color: #ffe58d;
    text-shadow: 0 0 18px rgba(255, 212, 95, 0.2);
}

.retro-metric-label {
    margin-top: 0.55rem;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.82rem, 1.2vw, 0.95rem);
    color: var(--retro-cyan);
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.retro-goal {
    margin: 0;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.9rem, 1.35vw, 1rem);
    color: var(--retro-muted);
    letter-spacing: 0.08em;
}

.retro-result-state {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 58px;
    padding: 0.9rem 1rem;
    border-radius: 14px;
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.88rem, 1.2vw, 1rem);
    letter-spacing: 0.16em;
    text-transform: uppercase;
    border: 1px solid transparent;
}

.retro-result-state--pass {
    color: var(--retro-green);
    background: rgba(120, 255, 143, 0.08);
    border-color: rgba(120, 255, 143, 0.18);
}

.retro-result-state--fail {
    color: #ffb6ba;
    background: rgba(255, 100, 106, 0.09);
    border-color: rgba(255, 100, 106, 0.18);
}

.retro-form-stack {
    display: grid;
    gap: 1rem;
}

.retro-input-label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--retro-cyan);
    font-family: "IBM Plex Mono", monospace;
    font-size: 0.92rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.retro-input-shell {
    display: flex;
    align-items: stretch;
    overflow: hidden;
    border-radius: 14px;
    border: 3px solid #0b0e15;
    box-shadow: 0 6px 0 rgba(0, 0, 0, 0.75);
}

.retro-input-shell .form-control {
    min-height: 54px;
    border: 0;
    border-radius: 0;
    background: rgba(246, 240, 223, 0.92);
    color: #0a1222;
    font-family: "IBM Plex Mono", monospace;
    font-size: 1rem;
    letter-spacing: 0.35em;
}

.retro-input-shell .form-control:focus {
    box-shadow: none;
}

.retro-input-shell .btn {
    min-width: 60px;
    border: 0;
    border-left: 2px solid #0b0e15;
    border-radius: 0;
    background: linear-gradient(180deg, #2ee3ff, #1a8cff);
    color: #08101b;
}

.retro-note {
    color: var(--retro-muted);
    font-size: 1.02rem;
    line-height: 1.45;
}

.retro-tape-bar {
    height: 14px;
    border-radius: 4px;
    border: 2px solid #0c0f16;
    background:
        repeating-linear-gradient(
            90deg,
            #22dff1 0 14px,
            #d43fd8 14px 28px,
            #ffe15f 28px 42px,
            #1c1f27 42px 56px
        );
    background-size: 112px 100%;
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.04);
    animation: retroTapeMove 1s linear infinite;
}

@keyframes retroTapeMove {
    from {
        background-position: 0 0;
    }
    to {
        background-position: 112px 0;
    }
}

@media (max-width: 640px) {
    .retro-page {
        padding: 0.8rem;
    }

    .retro-title {
        font-size: 1.6rem;
    }

    .retro-subtitle {
        font-size: 0.84rem;
        letter-spacing: 0.16em;
    }

    .retro-screen {
        min-height: 300px;
        padding: 0.9rem;
    }

    body.quiz-participant-shell {
        background-attachment: scroll;
        background-position: center center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.retro-screen').forEach((screen) => {
        if (screen.dataset.retroTransitionsBound === 'true') {
            return;
        }

        const beginExit = () => {
            if (screen.classList.contains('retro-screen--exiting')) {
                return;
            }

            screen.classList.add('retro-screen--exiting');
        };

        screen.dataset.retroTransitionsBound = 'true';

        screen.querySelectorAll('a[href]').forEach((anchor) => {
            anchor.addEventListener('click', (event) => {
                if (
                    event.defaultPrevented ||
                    anchor.target === '_blank' ||
                    anchor.hasAttribute('download') ||
                    anchor.getAttribute('href')?.startsWith('#') ||
                    event.metaKey ||
                    event.ctrlKey ||
                    event.shiftKey ||
                    event.altKey ||
                    event.button !== 0
                ) {
                    return;
                }

                event.preventDefault();
                beginExit();
                window.setTimeout(() => {
                    window.location.href = anchor.href;
                }, 170);
            });
        });

        screen.querySelectorAll('form').forEach((form) => {
            if (
                form.matches('[data-quiz-answer-form], [data-quiz-skip-form]') ||
                form.closest('[data-quiz-question-runtime]')
            ) {
                return;
            }

            form.addEventListener('submit', (event) => {
                if (form.dataset.retroSubmitting === 'true') {
                    return;
                }

                event.preventDefault();
                form.dataset.retroSubmitting = 'true';
                beginExit();
                window.setTimeout(() => form.submit(), 170);
            });
        });
    });
});
</script>
