@php
    $effectiveLanguage = $quiz->language === 'auto'
        ? app()->getLocale()
        : ($quiz->language ?? app()->getLocale());

    $alphabet = $effectiveLanguage === 'en'
        ? range('A', 'Z')
        : ['Α', 'Β', 'Γ', 'Δ', 'Ε', 'Ζ', 'Η', 'Θ', 'Ι', 'Κ', 'Λ', 'Μ', 'Ν', 'Ξ', 'Ο', 'Π', 'Ρ', 'Σ', 'Τ', 'Υ', 'Φ', 'Χ', 'Ψ', 'Ω'];

    $answerPrefix = $quiz->show_answer_numbering
        ? (($alphabet[$answerIndex] ?? (string) ($answerIndex + 1)) . '.')
        : null;
@endphp

@if($answerPrefix)
    <span class="fw-semibold me-2">{{ $answerPrefix }}</span>
@endif

<span>{{ $answer->text }}</span>
