@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    data-app-modal
    data-modal-name="{{ $name }}"
    data-modal-open="{{ $show ? 'true' : 'false' }}"
    @if($attributes->has('focusable')) data-modal-focusable="true" @endif
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    role="dialog"
    aria-modal="true"
    aria-hidden="{{ $show ? 'false' : 'true' }}"
    @unless($show) hidden @endunless
>
    <div data-modal-backdrop class="fixed inset-0 bg-gray-500 opacity-75"></div>

    <div data-modal-panel class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto" tabindex="-1">
        {{ $slot }}
    </div>
</div>
