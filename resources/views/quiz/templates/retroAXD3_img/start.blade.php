@extends('layouts.quiz_guest')

@section('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Noto+Sans+Display:wght@700;800;900&family=VT323&display=swap" rel="stylesheet">
@endsection

@section('content')
@include('quiz.templates.retroAXD3_img.partials.theme')

<style>
.retro-start-layout {
    display: grid;
    grid-template-rows: auto auto 1fr auto;
    gap: 1rem;
    min-height: 100%;
}

.retro-screen-bar--pause {
    color: #ffb347;
    text-shadow: 0 0 10px rgba(255, 179, 71, 0.35);
}

.retro-screen-bar__label--pause {
    gap: 0.65rem;
}

.retro-screen-bar__label--pause::before {
    display: none;
}

.retro-screen-bar__pause-icon {
    display: inline-grid;
    grid-auto-flow: column;
    gap: 4px;
    align-items: center;
}

.retro-screen-bar__pause-icon::before,
.retro-screen-bar__pause-icon::after {
    content: "";
    width: 4px;
    height: 12px;
    border-radius: 999px;
    background: currentColor;
    box-shadow: 0 0 8px currentColor;
}

.retro-start-screen {
    display: grid;
    align-content: center;
    justify-items: center;
    gap: clamp(0.85rem, 2vw, 1.35rem);
    min-height: var(--retro-stage-min-height);
    padding: clamp(0.25rem, 1vw, 0.75rem) 0 clamp(0.5rem, 1.5vw, 1rem);
    text-align: center;
}

.retro-start-counter-shell {
    position: relative;
    display: grid;
    place-items: center;
    width: min(100%, clamp(14rem, 40vw, 21rem));
    min-height: clamp(9rem, 24vw, 13rem);
    padding: clamp(1rem, 2.6vw, 1.6rem) clamp(1rem, 2vw, 1.4rem);
    border-radius: 24px;
    border: 3px solid rgba(255, 179, 71, 0.45);
    background:
        linear-gradient(180deg, rgba(255, 179, 71, 0.08), rgba(255, 79, 207, 0.05)),
        linear-gradient(180deg, rgba(5, 10, 20, 0.96), rgba(7, 11, 24, 0.92));
    box-shadow:
        inset 0 0 0 1px rgba(255, 255, 255, 0.06),
        inset 0 0 26px rgba(255, 179, 71, 0.12),
        0 0 28px rgba(255, 179, 71, 0.12),
        0 18px 40px rgba(0, 0, 0, 0.32);
    overflow: hidden;
}

.retro-start-counter-shell::before {
    content: "";
    position: absolute;
    inset: 0;
    background:
        linear-gradient(rgba(255, 255, 255, 0.06) 0, transparent 22%),
        repeating-linear-gradient(
            0deg,
            rgba(255, 255, 255, 0.05) 0 2px,
            transparent 2px 7px
        );
    opacity: 0.45;
    pointer-events: none;
}

.retro-start-counter-shell::after {
    content: "";
    position: absolute;
    inset: 10px;
    border: 1px solid rgba(89, 242, 255, 0.18);
    border-radius: 18px;
    box-shadow: inset 0 0 16px rgba(89, 242, 255, 0.08);
    pointer-events: none;
}

.retro-start-count {
    position: relative;
    z-index: 1;
    margin: 0;
    font-family: "Noto Sans Display", sans-serif;
    font-size: clamp(3.8rem, 10vw, 6.5rem);
    font-weight: 900;
    line-height: 1;
    color: #ffe58d;
    text-shadow:
        0 0 10px rgba(255, 229, 141, 0.78),
        0 0 28px rgba(255, 79, 207, 0.22),
        0 0 40px rgba(255, 179, 71, 0.18);
}

.retro-start-copy {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin: 0;
    padding: 0.6rem 1.15rem;
    min-width: clamp(10.5rem, 22vw, 12.5rem);
    max-width: min(100%, 24rem);
    border-radius: 999px;
    border: 1px solid rgba(89, 242, 255, 0.2);
    background: rgba(10, 18, 34, 0.72);
    color: var(--retro-text);
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.88rem, 1.15vw, 1rem);
    letter-spacing: 0.14em;
    text-transform: uppercase;
    box-shadow: 0 0 16px rgba(89, 242, 255, 0.06);
}

.retro-start-copy__accent {
    color: var(--retro-green);
    text-shadow: 0 0 10px rgba(120, 255, 143, 0.35);
}

.retro-start-sr {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}

.retro-start-legal {
    width: min(100%, 64rem);
    margin: 0 auto;
    padding: 0.8rem 1rem;
    border-radius: 16px;
    border: 1px solid rgba(89, 242, 255, 0.12);
    background:
        linear-gradient(180deg, rgba(255, 255, 255, 0.04), rgba(255, 255, 255, 0.02)),
        rgba(7, 11, 24, 0.7);
    box-shadow:
        inset 0 0 0 1px rgba(255, 255, 255, 0.03),
        0 0 18px rgba(89, 242, 255, 0.04);
}

.retro-start-legal p {
    margin: 0;
    color: rgba(246, 240, 223, 0.78);
    font-family: "IBM Plex Mono", monospace;
    font-size: clamp(0.68rem, 0.82vw, 0.8rem);
    line-height: 1.55;
    letter-spacing: 0.03em;
    text-wrap: pretty;
}

@media (max-width: 768px) {
    .retro-start-layout {
        gap: 0.8rem;
    }

    .retro-start-screen {
        min-height: var(--retro-stage-min-height-mobile);
        padding-bottom: 0.25rem;
    }

    .retro-start-copy {
        letter-spacing: 0.1em;
    }

    .retro-start-legal {
        padding: 0.75rem 0.85rem;
    }

    .retro-start-legal p {
        font-size: 0.72rem;
        line-height: 1.5;
    }
}

@media (max-width: 520px) {
    .retro-screen-bar {
        align-items: flex-start;
        gap: 0.55rem;
        margin-bottom: 0.65rem;
    }

    .retro-screen-meta {
        font-size: 0.72rem;
        letter-spacing: 0.12em;
    }

    .retro-screen-heading {
        gap: 0.2rem;
        margin-bottom: 0;
    }

    .retro-start-counter-shell {
        width: min(100%, 15.5rem);
        min-height: 8.5rem;
        border-radius: 20px;
    }

    .retro-start-copy {
        padding: 0.55rem 0.9rem;
        font-size: 0.82rem;
        gap: 0.35rem;
        min-width: 9.25rem;
    }

    .retro-start-legal {
        padding: 0.7rem 0.75rem;
        border-radius: 14px;
    }

    .retro-start-legal p {
        font-size: 0.68rem;
        line-height: 1.45;
    }
}
</style>

<div class="retro-page">
    <div class="retro-frame">
        <div class="retro-monitor fade-in" style="animation-delay: 0.1s;">
            <div class="retro-screen">
                <div class="retro-screen__inner retro-start-layout">
                    <div class="retro-screen-bar retro-screen-bar--pause">
                        <span class="retro-screen-bar__label retro-screen-bar__label--pause">
                            <span class="retro-screen-bar__pause-icon" aria-hidden="true"></span>
                            <span>Pause</span>
                        </span>
                        <span class="retro-screen-meta">guest mode</span>
                    </div>

                    <div class="retro-screen-heading">
                        <h1 class="retro-screen-heading__title">{{ $quiz->title }}</h1>
                        <p class="retro-screen-heading__subtitle">Retro AXD 3.0</p>
                    </div>

                    <div class="countdown-stage retro-start-screen"
                         data-countdown-redirect="{{ route('quiz.start_question', ['quizKey' => $quizRouteKey]) }}"
                         data-countdown-initial="10"
                         data-countdown-label="Starting in :seconds">
                        <div class="retro-start-counter-shell">
                            <span class="retro-start-count" data-countdown-value>10</span>
                        </div>
                        <p class="retro-start-copy">
                            <span>Start</span>
                            <span class="retro-start-copy__accent">Quiz</span>
                        </p>
                        <span class="retro-start-sr" data-countdown-text aria-live="polite">Starting in 10</span>
                    </div>

                    <div class="retro-start-legal">
                        <p>Το quiz δημιουργήθηκε αποκλειστικά για ψυχαγωγικούς σκοπούς στο πλαίσιο της εκδήλωσης "Retro AXD 3.0" του Συλλόγου Τεχνολογίας Θράκης και δεν έχει κερδοσκοπικό χαρακτήρα. Οι εικόνες και τα στιγμιότυπα προβάλλονται σε χαμηλή ανάλυση υπό το πρίσμα της «εύλογης χρήσης» (fair use) για λόγους σχολιασμού και νοσταλγίας. Δεν υπάρχει πρόθεση καταπάτησης πνευματικών δικαιωμάτων. Εάν είστε ο κάτοχος κάποιου έργου και επιθυμείτε την αφαίρεσή του, παρακαλούμε επικοινωνήστε μαζί μας.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
