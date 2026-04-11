<section class="dashboard-content-card dashboard-content-card--danger">
    <div class="dashboard-content-card__header">
        <div>
            <span class="dashboard-danger-chip">
                <i class="fas fa-triangle-exclamation"></i>
                {{ __('quizzes_cards.delete_title') }}
            </span>
            <h2 class="dashboard-content-card__title">{{ __('quizzes_cards.confirm_delete') }}</h2>
            <p class="dashboard-content-card__text">{{ __('quizzes_cards.delete_warning') }}</p>
        </div>
    </div>

    <button class="btn dashboard-btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
        <i class="fas fa-trash-alt me-2"></i>{{ __('quizzes_cards.delete_button') }}
    </button>
</section>

<div class="modal fade dashboard-modal" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ __('quizzes_cards.confirm_delete') }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-body">
                    <p>{{ __('quizzes_cards.delete_irreversible') }}</p>

                    <div class="mb-3">
                        <label for="delete_password" class="dashboard-form-label">
                            <i class="fas fa-key text-muted"></i> {{ __('quizzes_cards.password') }}
                        </label>
                        <input type="password" name="password" id="delete_password" class="form-control dashboard-form-control @error('password', 'userDeletion') is-invalid @enderror" required>
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn dashboard-btn dashboard-btn--ghost" data-bs-dismiss="modal">
                        <i class="fas fa-times-circle me-2"></i>{{ __('quizzes_cards.cancel') }}
                    </button>
                    <button type="submit" class="btn dashboard-btn btn-danger">
                        <i class="fas fa-trash me-2"></i>{{ __('quizzes_cards.confirm') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if ($errors->userDeletion->isNotEmpty())
    <div class="d-none" data-open-modal-on-load="deleteAccountModal"></div>
@endif