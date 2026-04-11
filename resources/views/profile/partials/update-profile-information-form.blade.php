<section class="dashboard-content-card">
    <div class="dashboard-content-card__header">
        <div>
            <span class="dashboard-section-card__eyebrow">
                <i class="fas fa-user-edit"></i>
                {{ __('quizzes_cards.update_title') }}
            </span>
            <h2 class="dashboard-content-card__title">{{ __('quizzes_cards.update_profile') }}</h2>
            <p class="dashboard-content-card__text">{{ __('quizzes_cards.profile_description') }}</p>
        </div>
    </div>

    @if (session('status') === 'profile-updated')
        <div class="dashboard-status-card dashboard-status-card--success mb-4">
            <i class="fas fa-check-circle"></i>
            <div>{{ __('quizzes_cards.profile_updated') }}</div>
        </div>
    @endif

    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
        <div class="dashboard-status-card dashboard-status-card--warning mb-4">
            <i class="fas fa-triangle-exclamation"></i>
            <div>
                <div>{{ __('quizzes_cards.email_not_verified') }}</div>
                <form method="POST" action="{{ route('verification.send') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-paper-plane me-2"></i>{{ __('quizzes_cards.send_verification_link') }}
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if (session('status') === 'verification-link-sent')
        <div class="dashboard-status-card dashboard-status-card--success mb-4">
            <i class="fas fa-envelope-circle-check"></i>
            <div>{{ __('quizzes_cards.verification_sent') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" class="dashboard-form-stack">
        @csrf
        @method('PATCH')

        <div class="dashboard-form-grid">
            <div class="dashboard-form-group">
                <label for="name" class="dashboard-form-label">
                    <i class="fas fa-user text-muted"></i>{{ __('quizzes_cards.name') }}
                </label>
                <input type="text" name="name" id="name" class="form-control dashboard-form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="dashboard-form-group">
                <label for="email" class="dashboard-form-label">
                    <i class="fas fa-envelope text-muted"></i>{{ __('quizzes_cards.email') }}
                </label>
                <input type="email" name="email" id="email" class="form-control dashboard-form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="dashboard-form-actions dashboard-form-actions--end">
            <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                <i class="fas fa-save me-2"></i>{{ __('quizzes_cards.save_changes') }}
            </button>
        </div>
    </form>
</section>
