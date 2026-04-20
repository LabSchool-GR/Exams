@php
    $appName = config('app.name', __('navigation.app_name'));
    $appVersion = trim((string) config('app.version', 'dev')) ?: 'dev';
    $appLicense = trim((string) config('app.license', 'AGPL-3.0-or-later')) ?: 'AGPL-3.0-or-later';
@endphp

<footer class="app-footer-subtle text-center text-muted small">
    <hr>
    <p class="mb-2">
        {{ __('footer.product_tagline') }}
        <span class="mx-1">|</span>
        {{ __('footer.developed_by') }}
    </p>
    <p class="mb-0">
        {{ __('navigation.footer_version') }} <span class="fw-semibold">{{ $appVersion }}</span>
        <span class="mx-1">|</span>
        {{ __('navigation.footer_license') }} <span class="fw-semibold">{{ $appLicense }}</span>
        @include('layouts.partials.source_code_link')
        <span class="mx-1">|</span>
        <a href="{{ route('terms') }}" class="text-decoration-none text-muted">{{ __('navigation.terms') }}</a>
        <span class="mx-1">|</span>
        <a href="{{ route('privacy') }}" class="text-decoration-none text-muted">{{ __('navigation.privacy') }}</a>
    </p>
</footer>
