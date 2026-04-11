@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card dashboard-section-card--wide">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-plus-circle"></i>
                        {{ __('quizzes_cards.create_quiz') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ __('quizzes_cards.create_quiz') }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.quiz_collection_intro') }}</p>
                </div>

                <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--ghost">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes_cards.cancel') }}
                </a>
            </div>

            @if ($errors->any())
                <div class="dashboard-status-card dashboard-status-card--danger mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(Auth::user()->role === 'admin' || Auth::user()->role === 'teacher')
                <form action="{{ route('quizzes.store') }}" method="POST" enctype="multipart/form-data" class="dashboard-form-stack">
                    @csrf

                    <div class="dashboard-form-panel">
                        <h2 class="dashboard-form-panel__title">{{ __('quizzes_cards.title') }} / {{ __('quizzes_cards.description') }} / {{ __('quizzes_cards.category') }}</h2>

                        <div class="dashboard-form-group">
                            <label for="title" class="dashboard-form-label">
                                <i class="fas fa-heading text-muted"></i>{{ __('quizzes_cards.title') }}
                            </label>
                            <input type="text" id="title" name="title" maxlength="80" class="form-control dashboard-form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required placeholder="{{ __('quizzes_cards.title_placeholder') }}">
                            <div class="dashboard-form-help">{{ __('quizzes_cards.title_notice') }}</div>
                        </div>

                        <div class="dashboard-form-group">
                            <label for="description" class="dashboard-form-label">
                                <i class="fas fa-align-left text-muted"></i>{{ __('quizzes_cards.description') }}
                            </label>
                            <textarea name="description" id="description" rows="3" maxlength="200" class="form-control dashboard-form-control @error('description') is-invalid @enderror" placeholder="{{ __('quizzes_cards.description_hint') }}">{{ old('description') }}</textarea>
                            <div class="dashboard-form-help">{{ __('quizzes_cards.description_notice') }}</div>
                        </div>

                        <div class="dashboard-form-group">
                            <label for="category_id" class="dashboard-form-label">
                                <i class="fas fa-folder-open text-muted"></i>{{ __('quizzes_cards.category') }}
                            </label>
                            <select name="category_id" id="category_id" class="form-select dashboard-form-control @error('category_id') is-invalid @enderror" required>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="dashboard-form-help">{{ __('quizzes_cards.category_notice') }}</div>
                        </div>
                    </div>

                    <div class="dashboard-status-card dashboard-status-card--warning">
                        <i class="fas fa-info-circle"></i>
                        <div>{{ __('quizzes_cards.code_notice') }}</div>
                    </div>

                    <div class="dashboard-form-panel">
                        <h2 class="dashboard-form-panel__title">{{ __('quizzes_cards.time_limit') }} / {{ __('quizzes_cards.pass_percentage') }} / {{ __('quizzes_cards.language') }}</h2>

                        <div class="dashboard-form-grid">
                            <div class="dashboard-form-group">
                                <label for="time_limit" class="dashboard-form-label">
                                    <i class="fas fa-clock text-muted"></i>{{ __('quizzes_cards.time_limit') }}
                                </label>
                                <input type="number" name="time_limit" id="time_limit" class="form-control dashboard-form-control @error('time_limit') is-invalid @enderror" value="{{ old('time_limit', 10) }}" min="1" required>
                                <div class="dashboard-form-help">{{ __('quizzes_cards.time_notice') }}</div>
                            </div>

                            <div class="dashboard-form-group">
                                <label for="pass_percentage" class="dashboard-form-label">
                                    <i class="fas fa-bullseye text-muted"></i>{{ __('quizzes_cards.pass_percentage') }}
                                </label>
                                <input type="number" name="pass_percentage" id="pass_percentage" class="form-control dashboard-form-control @error('pass_percentage') is-invalid @enderror" value="{{ old('pass_percentage', 50) }}" min="0" max="100" required>
                                <div class="dashboard-form-help">{{ __('quizzes_cards.pass_percentage_notice') }}</div>
                            </div>

                            <div class="dashboard-form-group">
                                <label for="question_view" class="dashboard-form-label">
                                    <i class="fas fa-paint-brush text-muted"></i>{{ __('quizzes_cards.question_view') }}
                                </label>
                                <select class="form-select dashboard-form-control @error('question_view') is-invalid @enderror" name="question_view" id="question_view" required>
                                    @foreach($templates as $template)
                                        <option value="{{ $template->code }}" {{ old('question_view') == $template->code ? 'selected' : '' }}>{{ $template->name }}</option>
                                    @endforeach
                                </select>
                                <div class="dashboard-form-help">{{ __('quizzes_cards.question_view_hint') }}</div>
                            </div>

                            <div class="dashboard-form-group">
                                <label for="language" class="dashboard-form-label">
                                    <i class="fas fa-globe text-muted"></i>{{ __('quizzes_cards.language') }}
                                </label>
                                <select name="language" id="language" class="form-select dashboard-form-control @error('language') is-invalid @enderror" required>
                                    <option value="el" {{ old('language') === 'el' ? 'selected' : '' }}>{{ __('quiz_editor.language_greek') }}</option>
                                    <option value="en" {{ old('language') === 'en' ? 'selected' : '' }}>{{ __('quiz_editor.language_english') }}</option>
                                    <option value="auto" {{ old('language', 'auto') === 'auto' ? 'selected' : '' }}>{{ __('quiz_editor.language_auto') }}</option>
                                </select>
                                <div class="dashboard-form-help">{{ __('quizzes_cards.language_hint') }}</div>
                            </div>

                            <div class="dashboard-form-group">
                                <label for="status" class="dashboard-form-label">
                                    <i class="fas fa-signal text-muted"></i>{{ __('quizzes_cards.status') }}
                                </label>
                                <select name="status" id="status" class="form-select dashboard-form-control @error('status') is-invalid @enderror">
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>{{ __('quizzes_cards.status_active') }}</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>{{ __('quizzes_cards.status_inactive') }}</option>
                                </select>
                                <div class="dashboard-form-help">{{ __('quizzes_cards.status_notice') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-form-panel">
                        <h2 class="dashboard-form-panel__title">{{ __('quizzes_cards.random_order') }} / {{ __('quizzes_cards.allow_resume') }} / {{ __('quizzes_cards.learning_mode') }}</h2>

                        <div class="dashboard-switch-grid">
                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="is_random_order" id="randomOrder" value="1" {{ old('is_random_order') ? 'checked' : '' }}>
                                <label class="form-check-label" for="randomOrder">{{ __('quizzes_cards.random_order') }}</label>
                            </div>

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="is_random_answers_order" id="randomAnswersOrder" value="1" {{ old('is_random_answers_order') ? 'checked' : '' }}>
                                <label class="form-check-label" for="randomAnswersOrder">{{ __('quiz_editor.random_answers_order') }}</label>
                            </div>

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="show_answer_numbering" id="showAnswerNumbering" value="1" {{ old('show_answer_numbering') ? 'checked' : '' }}>
                                <label class="form-check-label" for="showAnswerNumbering">{{ __('quiz_editor.show_answer_numbering') }}</label>
                            </div>

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="allow_resume" id="allowResume" value="1" {{ old('allow_resume', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="allowResume">{{ __('quizzes_cards.allow_resume') }}</label>
                            </div>

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="is_learning_mode" id="isLearningMode" value="1" {{ old('is_learning_mode') ? 'checked' : '' }}>
                                <label class="form-check-label" for="isLearningMode">{{ __('quizzes_cards.learning_mode') }}</label>
                                <div class="form-text">{{ __('quizzes_cards.learning_mode_notice') }}</div>
                            </div>

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="notify_creator_on_pass" id="notifyCreatorOnPass" value="1" {{ old('notify_creator_on_pass', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="notifyCreatorOnPass">{{ __('quizzes_cards.notify_creator_on_pass') }}</label>
                                <div class="form-text">{{ __('quizzes_cards.notify_creator_on_pass_notice') }}</div>
                            </div>

                            @if (Auth::user()->isAdmin())
                                <div class="form-check form-switch dashboard-switch-card">
                                    <input class="form-check-input" type="checkbox" name="is_certificate_verification_enabled" id="isCertificateVerificationEnabled" value="1" {{ old('is_certificate_verification_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isCertificateVerificationEnabled">{{ __('quizzes_cards.certificate_verification') }}</label>
                                    <div class="form-text">{{ __('quizzes_cards.certificate_verification_notice') }}</div>
                                </div>

                                <div class="form-check form-switch dashboard-switch-card">
                                    <input class="form-check-input" type="checkbox" name="is_second_screen_enabled" id="isSecondScreenEnabled" value="1" {{ old('is_second_screen_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isSecondScreenEnabled">{{ __('display.mode_label') }}</label>
                                    <div class="form-text">{{ __('display.mode_notice') }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="dashboard-form-panel">
                        <h2 class="dashboard-form-panel__title">
                            {{ __('quizzes_cards.allow_guest') }}
                            @if (Auth::user()->isAdmin())
                                / {{ __('quizzes_cards.anonymous_bulk_mode') }} / {{ __('quizzes_cards.public_anonymous_pool_mode') }}
                            @endif
                            / {{ __('quizzes_cards.has_timer') }}
                        </h2>

                        <div class="dashboard-switch-grid">
                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="allow_guest" id="allowGuest" value="1" {{ old('allow_guest') ? 'checked' : '' }}>
                                <label class="form-check-label" for="allowGuest">{{ __('quizzes_cards.allow_guest') }}</label>
                            </div>

                            @if (Auth::user()->isAdmin())
                                <div class="form-check form-switch dashboard-switch-card">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous_bulk_mode" id="isAnonymousBulkMode" value="1" {{ old('is_anonymous_bulk_mode') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isAnonymousBulkMode">{{ __('quizzes_cards.anonymous_bulk_mode') }}</label>
                                    <div class="form-text">{{ __('quizzes_cards.anonymous_bulk_mode_notice') }}</div>
                                </div>

                                <div class="form-check form-switch dashboard-switch-card">
                                    <input class="form-check-input" type="checkbox" name="is_public_anonymous_pool_mode" id="isPublicAnonymousPoolMode" value="1" {{ old('is_public_anonymous_pool_mode') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="isPublicAnonymousPoolMode">{{ __('quizzes_cards.public_anonymous_pool_mode') }}</label>
                                    <div class="form-text">{{ __('quizzes_cards.public_anonymous_pool_mode_notice') }}</div>
                                </div>
                            @endif

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="is_public" id="is_public" value="1" {{ old('is_public') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_public">{{ __('quizzes.is_public') }}</label>
                                <div class="form-text">{{ __('quizzes.is_public_hint') }}</div>
                            </div>

                            <div class="form-check form-switch dashboard-switch-card">
                                <input class="form-check-input" type="checkbox" name="has_timer" id="hasTimer" value="1" {{ old('has_timer', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="hasTimer">{{ __('quizzes_cards.has_timer') }}</label>
                                <div class="form-text">{{ __('quizzes_cards.has_timer_notice') }}</div>
                            </div>
                        </div>

                        @if (Auth::user()->isAdmin())
                            <div class="dashboard-form-group mt-3">
                                <label for="anonymous_pool_capacity" class="dashboard-form-label">
                                    <i class="fas fa-users text-muted"></i>{{ __('quizzes.anonymous_pool_capacity') }}
                                </label>
                                <input type="number" name="anonymous_pool_capacity" id="anonymous_pool_capacity" min="1" max="9999" value="{{ old('anonymous_pool_capacity', 100) }}" class="form-control dashboard-form-control">
                                <div class="dashboard-form-help">{{ __('quizzes.anonymous_pool_capacity_hint') }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="dashboard-form-panel">
                        <h2 class="dashboard-form-panel__title">{{ __('quizzes_cards.student_access_policy') }}</h2>

                        <div class="dashboard-form-group">
                            <label for="student_access_policy" class="dashboard-form-label">
                                <i class="fas fa-user-shield text-muted"></i>{{ __('quizzes_cards.student_access_policy') }}
                            </label>
                            <select name="student_access_policy" id="student_access_policy" class="form-select dashboard-form-control">
                                <option value="{{ \App\Models\Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS }}" {{ old('student_access_policy', \App\Models\Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS) === \App\Models\Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS ? 'selected' : '' }}>
                                    {{ __('quizzes_cards.student_access_policy_pin_and_links') }}
                                </option>
                                <option value="{{ \App\Models\Quiz::STUDENT_ACCESS_POLICY_PIN_ONLY }}" {{ old('student_access_policy') === \App\Models\Quiz::STUDENT_ACCESS_POLICY_PIN_ONLY ? 'selected' : '' }}>
                                    {{ __('quizzes_cards.student_access_policy_pin_only') }}
                                </option>
                                <option value="{{ \App\Models\Quiz::STUDENT_ACCESS_POLICY_LINKS_ONLY }}" {{ old('student_access_policy') === \App\Models\Quiz::STUDENT_ACCESS_POLICY_LINKS_ONLY ? 'selected' : '' }}>
                                    {{ __('quizzes_cards.student_access_policy_links_only') }}
                                </option>
                            </select>
                            <div class="dashboard-form-help">{{ __('quizzes_cards.student_access_policy_hint') }}</div>
                        </div>
                    </div>

                    <div class="dashboard-form-panel">
                        <h2 class="dashboard-form-panel__title">{{ __('quizzes_cards.quiz_image') }}</h2>

                        <div class="dashboard-form-group">
                            <label for="image" class="dashboard-form-label">
                                <i class="fas fa-image text-muted"></i>{{ __('quizzes_cards.quiz_image') }}
                            </label>
                            <input type="file" name="image" id="image" class="form-control dashboard-form-control @error('image') is-invalid @enderror" accept="image/*" data-image-preview-target="image-preview">
                            <img id="image-preview" src="#" data-preview-fallback-src="" class="mt-3 img-fluid dashboard-media-preview d-none" alt="Image Preview">
                            <div class="dashboard-form-help">{{ __('quizzes_cards.quiz_image_hint') }}</div>
                        </div>
                    </div>

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary">
                            <i class="fas fa-save me-2"></i>{{ __('quizzes_cards.save_quiz') }}
                        </button>
                    </div>
                </form>
            @else
                <div class="dashboard-status-card dashboard-status-card--danger">
                    <i class="fas fa-triangle-exclamation"></i>
                    <div>{{ __('quizzes_cards.not_allowed') }}</div>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection
