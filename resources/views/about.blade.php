@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 72rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-circle-info"></i>
                        LabSchool Exams
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('about.title') }}</h1>
                </div>
            </div>

            <div class="dashboard-content-card">
                <div class="dashboard-richtext">
                    {!! __('about.description_html') !!}
                </div>
            </div>

            <p class="dashboard-page-footer-note">{{ __('about.credits') }}</p>
        </section>
    </div>
</div>
@endsection
