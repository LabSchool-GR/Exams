@if (filled(config('app.source_url')))
    <span class="mx-1">|</span>
    <a
        href="{{ config('app.source_url') }}"
        class="text-decoration-none text-muted"
        target="_blank"
        rel="noopener noreferrer"
    >
        {{ __('navigation.source_code') }}
    </a>
@endif
