@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 72rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-scale-balanced"></i>
                        LabSchool Exams
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('terms.title') }}</h1>
                </div>
            </div>

            <div class="dashboard-content-card">
                <div class="dashboard-richtext">
                    <p>{!! __('terms.intro_html') !!}</p>

                    <hr>

                    <h4>{{ __('terms.section_usage') }}</h4>
                    <p>{!! __('terms.usage_html', ['domains' => e(config('security.registration.allowed_email_domains_display'))]) !!}</p>

                    <h4>{{ __('terms.section_content') }}</h4>
                    <p>{!! __('terms.content_html') !!}</p>

                    <h4>{{ __('terms.section_data') }}</h4>
                    <p>{!! __('terms.data_html') !!}</p>

                    <h4>{{ __('terms.section_code') }}</h4>
                    <p>{!! __('terms.code_html') !!}</p>

                    <h4>{{ __('terms.section_acceptance') }}</h4>
                    <p>{!! __('terms.acceptance_html') !!}</p>
                </div>
            </div>

            <p class="dashboard-page-footer-note">{{ __('terms.footer_note') }}</p>
        </section>
    </div>
</div>
@endsection
