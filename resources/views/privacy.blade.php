@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--form" style="max-width: 72rem;">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-page-header__eyebrow">
                        <i class="fas fa-shield-halved"></i>
                        LabSchool Exams
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('privacy.title') }}</h1>
                </div>
            </div>

            <div class="dashboard-content-card">
                <div class="dashboard-richtext">
                    <p>{!! __('privacy.intro_html') !!}</p>

                    <hr>

                    <h4>{{ __('privacy.section_controller') }}</h4>
                    <p>{!! __('privacy.controller_html') !!}</p>

                    <h4>{{ __('privacy.section_scope') }}</h4>
                    <p>{!! __('privacy.scope_html') !!}</p>

                    <h4>{{ __('privacy.section_purposes') }}</h4>
                    <p>{!! __('privacy.purposes_html') !!}</p>

                    <h4>{{ __('privacy.section_legal_basis') }}</h4>
                    <p>{!! __('privacy.legal_basis_html') !!}</p>

                    <h4>{{ __('privacy.section_recipients') }}</h4>
                    <p>{!! __('privacy.recipients_html') !!}</p>

                    <h4>{{ __('privacy.section_retention') }}</h4>
                    <p>{!! __('privacy.retention_html') !!}</p>

                    <h4>{{ __('privacy.section_rights') }}</h4>
                    <p>{!! __('privacy.rights_html') !!}</p>

                    <h4>{{ __('privacy.section_security') }}</h4>
                    <p>{!! __('privacy.security_html') !!}</p>

                    <h4>{{ __('privacy.section_updates') }}</h4>
                    <p>{!! __('privacy.updates_html') !!}</p>
                </div>
            </div>

            <p class="dashboard-page-footer-note">{{ __('privacy.footer_note') }}</p>
        </section>
    </div>
</div>
@endsection
