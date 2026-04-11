<section class="dashboard-content-card">
    <div class="dashboard-content-card__header">
        <div>
            <span class="dashboard-section-card__eyebrow">
                <i class="fas fa-key"></i>
                {{ __('quizzes_cards.password_title') }}
            </span>
            <h2 class="dashboard-content-card__title">{{ __('quizzes_cards.change_password') }}</h2>
            <p class="dashboard-content-card__text">{{ __('quizzes_cards.password_suggestion') }}</p>
        </div>
    </div>

    @if (session('status') === 'password-updated')
        <div class="dashboard-status-card dashboard-status-card--success mb-4">
            <i class="fas fa-check-circle"></i>
            <div>{{ __('quizzes_cards.password_updated') }}</div>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="dashboard-form-stack">
        @csrf
        @method('PUT')

        <div class="dashboard-form-group">
            <label for="current_password" class="dashboard-form-label">
                <i class="fas fa-lock text-muted"></i>{{ __('quizzes_cards.current_password') }}
            </label>
            <input type="password" name="current_password" id="current_password" class="form-control dashboard-form-control @error('current_password', 'updatePassword') is-invalid @enderror" required>
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="dashboard-form-grid">
            <div class="dashboard-form-group">
                <label for="password" class="dashboard-form-label">
                    <i class="fas fa-unlock-alt text-muted"></i>{{ __('quizzes_cards.new_password') }}
                </label>
                <input type="password" name="password" id="password" class="form-control dashboard-form-control @error('password', 'updatePassword') is-invalid @enderror" required>
                @error('password', 'updatePassword')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="dashboard-form-group">
                <label for="password_confirmation" class="dashboard-form-label">
                    <i class="fas fa-check-circle text-muted"></i>{{ __('quizzes_cards.password_confirmation') }}
                </label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control dashboard-form-control" required>
            </div>
        </div>

        <div class="dashboard-form-actions dashboard-form-actions--end">
            <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                <i class="fas fa-save me-2"></i>{{ __('quizzes_cards.save_password') }}
            </button>
        </div>
    </form>
</section>
