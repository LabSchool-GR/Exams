@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card mb-4">
            <div class="dashboard-page-header dashboard-page-header--update-center">
                <div class="dashboard-page-header__top">
                    <div class="dashboard-page-header__main">
                        <span class="dashboard-section-card__eyebrow">
                            <i class="fas fa-cloud-arrow-down"></i>
                            {{ __('system_updates.title') }}
                        </span>
                        <h1 class="dashboard-page-header__title">{{ __('system_updates.heading') }}</h1>
                    </div>

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <a href="{{ route('system_updates.index', ['refresh' => 1]) }}" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-rotate-right me-2"></i>{{ __('system_updates.refresh') }}
                        </a>
                    </div>
                </div>

                <p class="dashboard-page-header__text dashboard-page-header__text--single-line mb-0">{{ __('system_updates.intro') }}</p>
            </div>

            @switch($updateStatus['status'])
                @case('update_available')
                    <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                        <i class="fas fa-arrow-up-right-dots"></i>
                        <div>{{ __('system_updates.status_update_available', ['version' => $updateStatus['latest_release']['version'] ?? '-']) }}</div>
                    </div>
                    @break

                @case('up_to_date')
                    <div class="dashboard-status-card dashboard-status-card--success mb-4">
                        <i class="fas fa-circle-check"></i>
                        <div>{{ __('system_updates.status_up_to_date') }}</div>
                    </div>
                    @break

                @case('ahead_of_latest')
                    <div class="dashboard-status-card dashboard-status-card--success mb-4">
                        <i class="fas fa-code-branch"></i>
                        <div>{{ __('system_updates.status_ahead_of_latest') }}</div>
                    </div>
                    @break

                @case('comparison_unavailable')
                    <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                        <i class="fas fa-circle-info"></i>
                        <div>{{ __('system_updates.status_comparison_unavailable') }}</div>
                    </div>
                    @break

                @case('release_unavailable')
                    <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                        <i class="fas fa-circle-info"></i>
                        <div>{{ __('system_updates.status_release_unavailable') }}</div>
                    </div>
                    @break

                @case('not_configured')
                    <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                        <i class="fas fa-sliders"></i>
                        <div>{{ __('system_updates.status_not_configured') }}</div>
                    </div>
                    @break

                @case('disabled')
                    <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                        <i class="fas fa-power-off"></i>
                        <div>{{ __('system_updates.status_disabled') }}</div>
                    </div>
                    @break

                @case('error')
                    <div class="dashboard-status-card dashboard-status-card--danger mb-4">
                        <i class="fas fa-triangle-exclamation"></i>
                        <div>
                            {{ __('system_updates.status_error') }}
                            @if (!empty($updateStatus['error']))
                                <div class="dashboard-form-help mt-1">{{ $updateStatus['error'] }}</div>
                            @endif
                        </div>
                    </div>
                    @break
            @endswitch

            <div class="dashboard-form-grid">
                <article class="dashboard-content-card">
                    <div class="dashboard-content-card__header">
                        <div>
                            <h2 class="dashboard-content-card__title">{{ __('system_updates.installed_version_title') }}</h2>
                            <p class="dashboard-content-card__text">{{ __('system_updates.installed_version_help') }}</p>
                        </div>
                    </div>

                    <div class="dashboard-update-center__meta">
                        <strong class="dashboard-update-center__value">{{ $updateStatus['current_version'] }}</strong>
                        <p class="dashboard-form-help mb-0">
                            {{ __('system_updates.update_source') }}:
                            <span class="fw-semibold">{{ __('system_updates.source_' . ($updateStatus['source'] ?? 'unknown')) }}</span>
                        </p>
                        @if (!empty($updateStatus['manifest_url']))
                            <p class="dashboard-form-help mb-0">
                                {{ __('system_updates.manifest_url') }}:
                                <span class="dashboard-code-inline">{{ $updateStatus['manifest_url'] }}</span>
                            </p>
                        @endif
                        <p class="dashboard-form-help mb-0">
                            {{ __('system_updates.repository') }}:
                            <span class="dashboard-code-inline">{{ $updateStatus['repository'] ?: __('system_updates.not_available') }}</span>
                        </p>
                        @if (!empty($updateStatus['checked_at']))
                            <p class="dashboard-form-help mb-0">
                                {{ __('system_updates.checked_at') }}:
                                <span class="fw-semibold">{{ \Illuminate\Support\Carbon::parse($updateStatus['checked_at'])->setTimezone(config('app.timezone'))->format('d/m/Y H:i') }}</span>
                            </p>
                        @endif
                    </div>
                </article>

                <article class="dashboard-content-card">
                    <div class="dashboard-content-card__header">
                        <div>
                            <h2 class="dashboard-content-card__title">{{ __('system_updates.latest_release_title') }}</h2>
                            <p class="dashboard-content-card__text">{{ __('system_updates.latest_release_help') }}</p>
                        </div>
                    </div>

                    @if (!empty($updateStatus['latest_release']))
                        <div class="dashboard-update-center__meta">
                            <strong class="dashboard-update-center__value">{{ $updateStatus['latest_release']['version'] ?: __('system_updates.not_available') }}</strong>
                            <p class="dashboard-form-help mb-0">
                                {{ __('system_updates.release_name') }}:
                                <span class="fw-semibold">{{ $updateStatus['latest_release']['name'] ?: __('system_updates.not_available') }}</span>
                            </p>
                            @if (!empty($updateStatus['latest_release']['published_at_label']))
                                <p class="dashboard-form-help mb-0">
                                    {{ __('system_updates.published_at') }}:
                                    <span class="fw-semibold">{{ $updateStatus['latest_release']['published_at_label'] }}</span>
                                </p>
                            @endif
                            @if (!empty($updateStatus['latest_release']['download_name']))
                                <p class="dashboard-form-help mb-0">
                                    {{ __('system_updates.package_name') }}:
                                    <span class="dashboard-code-inline">{{ $updateStatus['latest_release']['download_name'] }}</span>
                                </p>
                            @endif
                        </div>

                        <div class="dashboard-form-actions mt-4">
                            @if (!empty($updateStatus['latest_release']['download_url']))
                                <a
                                    href="{{ $updateStatus['latest_release']['download_url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn dashboard-btn dashboard-btn--primary"
                                >
                                    <i class="fas fa-download me-2"></i>{{ __('system_updates.download_package') }}
                                </a>
                            @endif

                            @if (!empty($updateStatus['latest_release']['url']))
                                <a
                                    href="{{ $updateStatus['latest_release']['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="btn dashboard-btn dashboard-btn--ghost"
                                >
                                    <i class="fas fa-up-right-from-square me-2"></i>{{ __('system_updates.view_release') }}
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="dashboard-empty-state dashboard-empty-state--compact">
                            <div class="dashboard-empty-state__icon">
                                <i class="fas fa-box-open"></i>
                            </div>
                            <h3 class="dashboard-empty-state__title">{{ __('system_updates.latest_release_empty_title') }}</h3>
                            <p class="dashboard-empty-state__text">{{ __('system_updates.latest_release_empty_text') }}</p>
                        </div>
                    @endif
                </article>
            </div>
        </section>

        <section class="dashboard-section-card">
            <div class="dashboard-page-header dashboard-page-header--stack">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-scroll"></i>
                        {{ __('system_updates.changelog_title') }}
                    </span>
                    <h2 class="dashboard-page-header__title">{{ __('system_updates.changelog_heading') }}</h2>
                    <p class="dashboard-page-header__text">{{ __('system_updates.changelog_help') }}</p>
                </div>
            </div>

            @if (!empty($updateStatus['latest_release']['notes']))
                <div class="dashboard-update-center__changelog">{!! nl2br(e($updateStatus['latest_release']['notes'])) !!}</div>
            @else
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-note-sticky"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('system_updates.changelog_empty_title') }}</h3>
                    <p class="dashboard-empty-state__text">{{ __('system_updates.changelog_empty_text') }}</p>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
