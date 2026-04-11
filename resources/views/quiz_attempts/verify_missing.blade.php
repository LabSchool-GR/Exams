@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card" style="max-width: 48rem; margin-inline: auto;">
            <div class="dashboard-empty-state dashboard-empty-state--compact">
                <div class="dashboard-empty-state__icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h1 class="dashboard-empty-state__title">{{ __('verify.title') }}</h1>
                <p class="dashboard-empty-state__text">{{ __('verify.invalid_attempt', ['id' => $attempt_id]) }}</p>
            </div>
        </section>
    </div>
</div>
@endsection
