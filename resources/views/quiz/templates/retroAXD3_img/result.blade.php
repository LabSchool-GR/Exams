@extends('layouts.quiz_guest')

@section('meta')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&family=Noto+Sans+Display:wght@700;800;900&family=VT323&display=swap" rel="stylesheet">
@endsection

@section('content')
@include('quiz.templates.retroAXD3_img.partials.theme')

<style>
.retro-result-layout {
    display: grid;
    gap: 1rem;
}

.retro-screen-bar--stop {
    color: var(--retro-red);
    text-shadow: 0 0 10px rgba(255, 100, 106, 0.35);
}

.retro-screen-bar__label--stop {
    gap: 0.65rem;
}

.retro-screen-bar__label--stop::before {
    display: none;
}

.retro-screen-bar__stop-icon {
    width: 11px;
    height: 11px;
    border-radius: 2px;
    background: currentColor;
    box-shadow: 0 0 8px currentColor;
}

.retro-result-screen {
    min-height: var(--retro-stage-min-height);
    display: grid;
    align-content: center;
    justify-items: center;
    text-align: center;
    gap: 1.05rem;
}

.retro-result-divider {
    width: min(100%, 180px);
    height: 2px;
    background: linear-gradient(90deg, transparent, var(--retro-cyan), transparent);
}

.retro-result-state {
    width: min(100%, 54rem);
    min-height: 72px;
    padding: 1rem 1.2rem;
    line-height: 1.5;
}

.retro-result-copy {
    width: min(100%, 42rem);
    margin: 0;
    color: var(--retro-text);
    font-size: clamp(1rem, 1.5vw, 1.15rem);
    line-height: 1.5;
}

.retro-code-panel {
    width: min(100%, 26rem);
    padding: 1rem 1.1rem;
    border-radius: 18px;
    border: 1px solid rgba(120, 255, 143, 0.24);
    background:
        linear-gradient(180deg, rgba(120, 255, 143, 0.08), rgba(89, 242, 255, 0.05)),
        rgba(7, 12, 24, 0.82);
    box-shadow:
        inset 0 0 0 1px rgba(255, 255, 255, 0.04),
        0 0 24px rgba(120, 255, 143, 0.08);
}

.retro-code-label {
    margin: 0;
    color: var(--retro-cyan);
    font-family: "IBM Plex Mono", monospace;
    font-size: 0.88rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
}

.retro-code-value {
    margin: 0.55rem 0 0;
    color: #ffe58d;
    font-family: "Noto Sans Display", sans-serif;
    font-size: clamp(2rem, 5vw, 3rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: 0.16em;
    text-shadow:
        0 0 10px rgba(255, 229, 141, 0.7),
        0 0 24px rgba(120, 255, 143, 0.16);
}

.retro-code-note {
    margin: 0.7rem 0 0;
    color: var(--retro-muted);
    font-family: "IBM Plex Mono", monospace;
    font-size: 0.84rem;
    line-height: 1.45;
}

.retro-result-actions {
    width: min(100%, 320px);
    display: grid;
    gap: 0.75rem;
}

.retro-action__icon {
    position: relative;
    display: inline-block;
    flex: 0 0 auto;
}

.retro-action__icon--rewind {
    width: 18px;
    height: 12px;
}

.retro-action__icon--rewind::before,
.retro-action__icon--rewind::after {
    content: "";
    position: absolute;
    top: 0;
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-right: 8px solid currentColor;
}

.retro-action__icon--rewind::before {
    left: 0;
}

.retro-action__icon--rewind::after {
    left: 8px;
}

.retro-action__label {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.7rem;
}

.retro-action--ghost {
    background: linear-gradient(180deg, #18233a, #0c1425);
    color: var(--retro-text);
}

@media (max-width: 640px) {
    .retro-result-screen {
        min-height: var(--retro-stage-min-height-mobile);
    }

    .retro-result-state {
        padding: 0.9rem 0.9rem;
        font-size: 0.84rem;
        letter-spacing: 0.11em;
    }

    .retro-code-panel {
        padding: 0.9rem;
    }

    .retro-code-value {
        font-size: clamp(1.7rem, 9vw, 2.4rem);
        letter-spacing: 0.12em;
    }
}
</style>

@php
    $isLearningModeResult = $isLearningModeResult ?? false;
    $passedQuiz = !$isLearningModeResult && $scorePercentage >= $quiz->pass_percentage;
    $roundedScorePercentage = (int) round($scorePercentage);
    $resolvedLocale = $quiz->resolvedLocale(app()->getLocale());
    $isEnglish = \Illuminate\Support\Str::startsWith($resolvedLocale, 'en');

    $passHeadline = $isEnglish ? 'You Are Cool!' : 'Είσαι Cool!';
    $passCopy = $isEnglish
        ? 'Keep this retro pass code and show it at the event desk for your gift.'
        : 'Κράτα φωτογραφία αυτόν τον retro-κωδικό και δείξ’ τον στην εκδήλωση για το δώρο σου.';
    $codeLabel = $isEnglish ? 'Event Pass Code' : 'Κωδικός Δώρου';
    $passHint = $isEnglish
        ? 'Event gift codes in this template always end in 45.'
        : 'Η προσφορά των δώρων ισχύει μέχρι την εξάντληση των αποθεμάτων.';
    $failCopy = $isEnglish
        ? 'No stress. Rewind the tape and give it another go.'
        : 'Δεν πειράζει. Γύρνα την κασέτα πίσω και δοκίμασέ το ξανά.';
    $retryLabel = $isEnglish ? 'Retry Round' : 'Προσπάθεια Ξανά';

    $giftCode = null;
    if ($passedQuiz) {
        $giftChecksumTarget = 27;
        $giftSuffix = '45';
        $giftSuffixSum = array_sum(array_map('intval', str_split($giftSuffix)));
        $giftSeedSource = implode('|', [
            (string) $quiz->id,
            (string) ($attempt->id ?? 'guest'),
            (string) ($attempt->student_code ?? '0000'),
            (string) ($attempt->student_name ?? ''),
            (string) $roundedScorePercentage,
            (string) $correctCount,
            (string) $totalQuestions,
        ]);

        $giftSeed = abs(crc32($giftSeedSource));
        $digitOne = $giftSeed % 10;
        $digitTwo = intdiv($giftSeed, 10) % 10;
        $remainingPrefixSum = max(0, ($giftChecksumTarget - $giftSuffixSum) - $digitOne - $digitTwo);
        $digitThree = min(9, $remainingPrefixSum);
        $digitFour = max(0, min(9, $remainingPrefixSum - $digitThree));

        $giftPrefix = implode('', [$digitOne, $digitTwo, $digitThree, $digitFour]);
        $giftCode = $giftPrefix.$giftSuffix;
    }

    $canRetryAsGuest = !$isLearningModeResult
        && $attempt->student_code === '0000'
        && $scorePercentage < $quiz->pass_percentage
        && $quiz->is_public
        && $quiz->allow_guest;

    $canRetryAsStudent = !$isLearningModeResult
        && $attempt->student_code !== '0000'
        && $scorePercentage < $quiz->pass_percentage
        && $remainingAttempts > 0
        && $quiz->supportsStudentPersonalLinks();

    $retryUrl = null;

    if ($canRetryAsGuest) {
        $retryUrl = $quiz->publicAccessUrl();
    } elseif ($canRetryAsStudent && !empty($attempt->student_code)) {
        $student = $attempt->student;

        if (!$student) {
            $student = \App\Models\QuizStudent::where('quiz_id', $quiz->id)
                ->where('student_code', $attempt->student_code)
                ->first();
        }

        if ($student) {
            $retryUrl = $student->accessLinkUrl();
        }
    }
@endphp

<div class="retro-page">
    <div class="retro-frame">
        <div class="retro-monitor fade-in" style="animation-delay: 0.1s;">
            <div class="retro-screen">
                <div class="retro-screen__inner retro-result-layout">
                    <div class="retro-screen-bar retro-screen-bar--stop">
                        <span class="retro-screen-bar__label retro-screen-bar__label--stop">
                            <span class="retro-screen-bar__stop-icon" aria-hidden="true"></span>
                            <span>STOP</span>
                        </span>
                        <span class="retro-screen-meta">{{ __('join.results') }}</span>
                    </div>

                    <div class="retro-screen-heading">
                        <h1 class="retro-screen-heading__title">{{ $quiz->title }}</h1>
                        <p class="retro-screen-heading__subtitle">Retro AXD 3.0</p>
                    </div>

                    <div class="retro-result-screen">
                        @if($isLearningModeResult)
                            <div class="retro-panel">
                                <p class="retro-note mb-0">{{ __('join.learning_mode_result_message') }}</p>
                            </div>
                        @else
                            <div class="retro-metric-value">{{ $roundedScorePercentage }}%</div>
                            <div class="retro-metric-label">{{ __('join.score_percentage', ['score' => $roundedScorePercentage]) }}</div>
                            <div class="retro-result-divider"></div>
                            <p class="retro-goal">{{ __('join.pass_threshold_note', ['required' => (int) $quiz->pass_percentage]) }}</p>

                            @if($passedQuiz)
                                <div class="retro-result-state retro-result-state--pass">
                                    {{ $passHeadline }}
                                </div>
                                <p class="retro-result-copy">{{ $passCopy }}</p>
                                <div class="retro-code-panel">
                                    <p class="retro-code-label">{{ $codeLabel }}</p>
                                    <p class="retro-code-value">{{ $giftCode }}</p>
                                    <p class="retro-code-note">{{ $passHint }}</p>
                                </div>
                            @else
                                <div class="retro-result-state retro-result-state--fail">
                                    {{ $failCopy }}
                                </div>
                            @endif
                        @endif

                        <div class="retro-result-actions">
                            @if($retryUrl)
                                <a href="{{ $retryUrl }}" class="retro-action retro-action--primary">
                                    <span class="retro-action__label">
                                        <span class="retro-action__icon retro-action__icon--rewind" aria-hidden="true"></span>
                                        <span>{{ $retryLabel }}</span>
                                    </span>
                                </a>
                            @endif

                            <a href="https://retro.steth.gr/" target="_blank" rel="noopener noreferrer" class="retro-action {{ $retryUrl ? 'retro-action--ghost' : 'retro-action--primary' }}">
                                Go Retro AXD 3.0
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
