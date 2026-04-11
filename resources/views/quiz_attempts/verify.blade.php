@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 48rem; margin-inline: auto;">
            <div class="dashboard-empty-state dashboard-empty-state--compact">
                <div class="dashboard-empty-state__icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="dashboard-empty-state__title">{{ __('verify.title') }}</h1>
                <p class="dashboard-empty-state__text">{{ __('verify.valid_attempt', ['id' => $attempt->id]) }}</p>
                <p class="dashboard-empty-state__text"><strong>{{ $quiz->title }}</strong></p>
                <p class="dashboard-empty-state__text">{{ __('verify.date') }}: <strong>{{ \Carbon\Carbon::parse($attempt->submitted_at)->format('d/m/Y') }}</strong></p>

                @if (!empty($isPublicVerification))
                    <p class="dashboard-empty-state__text">{{ __('verify.public_minimized_notice') }}</p>
                @else
                    <p class="dashboard-empty-state__text">{{ __('verify.student_completed', ['name' => $attempt->student_name]) }}</p>
                    <p class="dashboard-empty-state__text">{{ __('verify.score') }}: <strong>{{ $attempt->score }}%</strong></p>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection