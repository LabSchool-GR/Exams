@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-bullhorn"></i>
                        {{ __('dashboard.updates_title') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('dashboard.latest_updates') }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.updates_intro') }}</p>
                </div>

                <div class="dashboard-form-actions">
                    <a href="{{ route('dashboard') }}" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-arrow-left me-2"></i>{{ __('dashboard.back') }}
                    </a>

                    @can('manage-updates')
                        <a href="{{ route('updates.create') }}" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-plus-circle me-2"></i>{{ __('dashboard.updates_add') }}
                        </a>
                    @endcan
                </div>
            </div>

            @if (session('success'))
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if ($updates->isEmpty())
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-bell-slash"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('dashboard.updates_empty') }}</h3>
                </div>
            @else
                <div class="dashboard-updates-list">
                    @foreach ($updates as $index => $update)
                        <article class="dashboard-update-item">
                            <div class="dashboard-update-item__content">
                                <div class="dashboard-update-item__main">
                                    <div class="dashboard-update-item__date">
                                        #{{ $index + 1 }} | {{ $update->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                    <p class="dashboard-update-item__text mb-0">{{ $update->description }}</p>
                                </div>

                                <div class="dashboard-form-actions dashboard-form-actions--end">
                                    @if ($update->link)
                                        <a href="{{ $update->link }}" target="_blank" rel="noopener" class="btn dashboard-btn dashboard-btn--ghost">
                                            <i class="fas fa-up-right-from-square me-2"></i>{{ __('dashboard.updates_open_link') }}
                                        </a>
                                    @endif

                                    @can('manage-updates')
                                        <form method="POST" action="{{ route('updates.destroy', $update) }}" data-confirm-submit="{{ __('dashboard.confirm_delete') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn dashboard-btn btn-danger">
                                                <i class="fas fa-trash-alt me-2"></i>{{ __('dashboard.delete') }}
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
