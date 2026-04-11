@extends('layouts.navigation')

@section('content')
<div class="dashboard-shell dashboard-page-shell">
    <div class="container py-4 py-lg-5">
        <section class="dashboard-section-card mb-4">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-user-graduate"></i>
                        {{ __('quizzes.register_students') }}
                    </span>
                    <h1 class="dashboard-page-header__title">{{ $quiz->title }}</h1>
                    <p class="dashboard-page-header__text">{{ __('dashboard.quiz_collection_intro') }}</p>
                </div>

                <a href="{{ route('quizzes.index') }}" class="btn dashboard-btn dashboard-btn--ghost">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('quizzes.back_to_list') }}
                </a>
            </div>

            @if(session('success'))
                <div class="dashboard-status-card dashboard-status-card--success mb-4">
                    <i class="fas fa-check-circle"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="dashboard-status-card dashboard-status-card--danger mb-4">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>{!! nl2br(e(session('error'))) !!}</div>
                </div>
            @endif

            @if ($quiz->is_anonymous_bulk_mode)
                <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                    <i class="fas fa-user-secret"></i>
                    <div>{{ __('quizzes.anonymous_bulk_mode_hint') }}</div>
                </div>
            @endif

            @if ($quiz->is_public_anonymous_pool_mode)
                <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                    <i class="fas fa-qrcode"></i>
                    <div>{{ __('quizzes.public_anonymous_pool_hint') }}</div>
                </div>
            @endif

            @if ($students->isEmpty())
                <div class="dashboard-empty-state dashboard-empty-state--compact">
                    <div class="dashboard-empty-state__icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h3 class="dashboard-empty-state__title">{{ __('quizzes.no_students') }}</h3>
                </div>
            @else
                <div class="dashboard-collection-grid">
                    @php $attemptGroups = $attemptsGrouped; @endphp
                    @foreach ($students as $student)
                        @php
                            $code = $student->student_code;
                            $studentGroupKey = 'student:' . $student->id;
                            $legacyGroupKey = 'code:' . $code;
                            $attempts = $attemptGroups->get($studentGroupKey, $attemptGroups->get($legacyGroupKey, collect()));
                            $completed = $attempts->whereNotNull('submitted_at')->count();
                            $studentUrl = $code !== '0000' && $quiz->supportsStudentPersonalLinks()
                                ? $student->accessLinkUrl()
                                : null;
                            $displaySession = $displaySessionsByStudentId->get($student->id);
                            $screenUrl = $displaySession?->screenUrl();
                        @endphp

                        <article class="dashboard-collection-card">
                            <div class="dashboard-collection-card__main">
                                <div class="dashboard-collection-card__icon">
                                    <i class="fas fa-user"></i>
                                </div>

                                <div>
                                    <h2 class="dashboard-collection-card__title">{{ $student->student_name }}</h2>
                                    <div class="dashboard-collection-meta">
                                        <span class="dashboard-collection-pill">
                                            <i class="fas fa-key"></i>{{ $code }}
                                        </span>
                                        <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                            <i class="fas fa-chart-line"></i>{{ __('quizzes.attempts') }}: {{ $attempts->count() }}
                                        </span>
                                        <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                            <i class="fas fa-check-double"></i>{{ __('quizzes.completed_attempts') }}: {{ $completed }}
                                        </span>
                                        <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                            <i class="fas fa-repeat"></i>{{ __('quizzes.max_attempts') }}: {{ $student->max_attempts }}
                                        </span>
                                    </div>

                                    @if (!empty($studentUrl))
                                        <div class="mt-3">
                                            <input type="text" id="link-{{ $code }}" value="{{ $studentUrl }}" class="form-control dashboard-form-control d-none" tabindex="-1" readonly>
                                            <button
                                                type="button"
                                                class="btn dashboard-btn dashboard-btn--ghost"
                                                data-copy-target="link-{{ $code }}"
                                                data-copy-success="{{ __('ui.copy_link_success') }}"
                                                data-copy-error="{{ __('ui.copy_link_failed') }}"
                                            >
                                                <i class="fas fa-copy me-2"></i>{{ __('quizzes.copy_link') }}
                                            </button>
                                        </div>
                                    @endif

                                    @if ($quiz->usesSecondScreenMode() && !$student->is_anonymous)
                                        <div class="mt-3 d-flex flex-wrap gap-2 align-items-center">
                                            @if ($displaySession)
                                                <span class="dashboard-collection-pill dashboard-collection-pill--muted">
                                                    <i class="fas fa-tv"></i>
                                                    {{ $displaySession->isControllerClaimed() ? __('display.connected_badge') : __('display.waiting_badge') }}
                                                </span>
                                                <input type="text" id="screen-link-{{ $student->id }}" value="{{ $screenUrl }}" class="form-control dashboard-form-control d-none" tabindex="-1" readonly>
                                                <button
                                                    type="button"
                                                    class="btn dashboard-btn dashboard-btn--ghost"
                                                    data-copy-target="screen-link-{{ $student->id }}"
                                                    data-copy-success="{{ __('ui.copy_link_success') }}"
                                                    data-copy-error="{{ __('ui.copy_link_failed') }}"
                                                >
                                                    <i class="fas fa-display me-2"></i>{{ __('display.copy_screen_link') }}
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="dashboard-collection-actions">
                                @if ($quiz->usesSecondScreenMode() && !$student->is_anonymous)
                                    <form action="{{ route('quiz_display.launch', [$quiz, $student]) }}" method="POST" class="dashboard-inline-form" target="_blank">
                                        @csrf
                                        <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
                                            <i class="fas fa-tv me-2"></i>{{ $displaySession ? __('display.open_button') : __('display.launch_button') }}
                                        </button>
                                    </form>

                                    @if ($displaySession)
                                        <form action="{{ route('quiz_display.terminate', [$quiz, $displaySession]) }}" method="POST" data-confirm-submit="{{ __('display.terminate_confirm') }}">
                                            @csrf
                                            <button type="submit" class="btn dashboard-btn btn-warning">
                                                <i class="fas fa-stop-circle me-2"></i>{{ __('display.terminate_button') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif

                                <a href="{{ route('quiz_attempts.student_info_pdf', ['quiz' => $quiz->id, 'student_code' => $code]) }}" class="btn dashboard-btn dashboard-btn--ghost" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i>PDF
                                </a>

                                @if ($student->student_code !== '0000')
                                    <form action="{{ route('quiz_attempts.destroy_student', [$quiz, $student->id]) }}" method="POST" data-confirm-submit="{{ __('quiz_attempts.confirm_delete_attempts') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn dashboard-btn {{ $attempts->count() > 1 ? 'btn-warning' : 'btn-danger' }}">
                                            <i class="fas fa-trash-alt me-2"></i>{{ __('quiz_attempts.delete') }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end mt-4">
                    <a href="{{ route('quiz_attempts.students_report_pdf', $quiz) }}" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-print me-2"></i>{{ __('quizzes.print_students_list') }}
                    </a>
                    @if ($quiz->is_anonymous_bulk_mode)
                        <a href="{{ route('quiz_attempts.anonymous_cards_pdf', $quiz) }}" class="btn dashboard-btn dashboard-btn--ghost" target="_blank">
                            <i class="fas fa-qrcode me-2"></i>{{ __('quizzes.download_anonymous_cards_pdf') }}
                        </a>
                    @endif
                </div>
            @endif
        </section>

        @php $guestUrl = $quiz->publicAccessUrl(); @endphp
        @if (!empty($guestUrl))
            <section class="dashboard-section-card mb-4">
                <div class="dashboard-page-header">
                    <div>
                        <span class="dashboard-section-card__eyebrow">
                            <i class="fas fa-eye"></i>
                            {{ $quiz->is_public_anonymous_pool_mode ? __('quizzes.public_anonymous_pool_mode_label') : __('quizzes.guest_access') }}
                        </span>
                        <h2 class="dashboard-page-header__title">{{ $quiz->is_public_anonymous_pool_mode ? __('quizzes.public_anonymous_pool_link') : __('quizzes.guest_link') }}</h2>
                        <p class="dashboard-page-header__text">{{ $quiz->is_public_anonymous_pool_mode ? __('quizzes.public_anonymous_pool_link_hint') : __('quizzes.guest_link_hint') }}</p>
                    </div>
                </div>

                <div class="dashboard-form-group">
                    <input type="text" class="form-control dashboard-form-control" value="{{ $guestUrl }}" readonly id="guest-link">
                </div>

                <div class="dashboard-form-actions dashboard-form-actions--end">
                    <button
                        type="button"
                        class="btn dashboard-btn dashboard-btn--ghost"
                        data-copy-target="guest-link"
                        data-copy-success="{{ __('ui.copy_link_success') }}"
                        data-copy-error="{{ __('ui.copy_link_failed') }}"
                    >
                        <i class="fas fa-copy me-2"></i>{{ __('quizzes.copy') }}
                    </button>
                </div>
            </section>
        @endif

        <section class="dashboard-section-card mb-4">
            <div class="dashboard-page-header">
                <div>
                    <span class="dashboard-section-card__eyebrow">
                        <i class="fas fa-user-plus"></i>
                        {{ $quiz->is_anonymous_bulk_mode ? __('quizzes.create_anonymous_slots') : ($quiz->is_public_anonymous_pool_mode ? __('quizzes.public_anonymous_pool_mode_label') : __('quizzes.add_student')) }}
                    </span>
                    <h2 class="dashboard-page-header__title">{{ $quiz->is_anonymous_bulk_mode ? __('quizzes.create_anonymous_slots') : ($quiz->is_public_anonymous_pool_mode ? __('quizzes.public_anonymous_pool_mode_label') : __('quizzes.add_student')) }}</h2>
                </div>
            </div>

            @if(isset($studentLimit) && !auth()->user()?->isAdmin())
                <div class="dashboard-status-card dashboard-status-card--warning mb-4">
                    <i class="fas fa-circle-info"></i>
                    <div>{{ __('quizzes.student_limit_status', ['count' => $students->count(), 'limit' => $studentLimit]) }}</div>
                </div>
            @endif

            @if ($quiz->is_public_anonymous_pool_mode)
                <div class="dashboard-status-card dashboard-status-card--warning mb-0">
                    <i class="fas fa-circle-info"></i>
                    <div>{{ __('quizzes.public_anonymous_pool_dashboard_hint', ['count' => $publicAnonymousPoolCompletedCount ?? 0, 'capacity' => $quiz->anonymous_pool_capacity ?? 0]) }}</div>
                </div>
            @elseif ($quiz->is_anonymous_bulk_mode)
                <form action="{{ route('quiz_attempts.store_anonymous_students', $quiz) }}" method="POST" class="dashboard-form-stack">
                    @csrf

                    <div class="dashboard-form-grid">
                        <div class="dashboard-form-group">
                            <label for="anonymous_slots_count" class="dashboard-form-label">
                                <i class="fas fa-users text-muted"></i>{{ __('quizzes.anonymous_slots_count') }}
                            </label>
                            <input type="number" name="anonymous_slots_count" id="anonymous_slots_count" min="1" max="9999" value="1" class="form-control dashboard-form-control" required>
                            <div class="dashboard-form-help">{{ __('quizzes.anonymous_slots_count_hint') }}</div>
                        </div>

                        <div class="dashboard-form-group">
                            <label for="anonymous_max_attempts" class="dashboard-form-label">
                                <i class="fas fa-repeat text-muted"></i>{{ __('quizzes.max_attempts') }}
                            </label>
                            <input type="number" name="anonymous_max_attempts" id="anonymous_max_attempts" min="1" max="5" value="1" class="form-control dashboard-form-control" required>
                            <div class="dashboard-form-help">{{ __('quizzes.anonymous_max_attempts_hint') }}</div>
                        </div>
                    </div>

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary" @if(isset($canRegisterStudents) && !$canRegisterStudents) disabled @endif>
                            <i class="fas fa-qrcode me-2"></i>{{ __('quizzes.generate_anonymous_slots') }}
                        </button>
                    </div>
                </form>
            @else
                <form action="{{ route('quiz_attempts.store_student', $quiz) }}" method="POST" class="dashboard-form-stack">
                    @csrf

                    <div class="dashboard-form-grid">
                        <div class="dashboard-form-group">
                            <label for="student_name" class="dashboard-form-label">
                                <i class="fas fa-user text-muted"></i>{{ __('quizzes.student_name_attempts') }}
                            </label>
                            <input type="text" name="student_name" id="student_name" class="form-control dashboard-form-control" required>
                        </div>

                        <div class="dashboard-form-group">
                            <label for="student_code" class="dashboard-form-label">
                                <i class="fas fa-key text-muted"></i>{{ __('quizzes.student_code_attempts') }}
                            </label>
                            <input type="text" name="student_code" id="student_code" maxlength="4" class="form-control dashboard-form-control text-center" required>
                            <div class="dashboard-form-help">{{ __('quizzes.code_hint') }}</div>
                        </div>

                        <div class="dashboard-form-group">
                            <label for="max_attempts" class="dashboard-form-label">
                                <i class="fas fa-repeat text-muted"></i>{{ __('quizzes.max_attempts') }}
                            </label>
                            <input type="number" name="max_attempts" id="max_attempts" min="1" max="5" value="1" class="form-control dashboard-form-control" required>
                            <div class="dashboard-form-help">{{ __('ui.standard_attempts_help') }}</div>
                        </div>
                    </div>

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary" @if(isset($canRegisterStudents) && !$canRegisterStudents) disabled @endif>
                            <i class="fas fa-check me-2"></i>{{ __('quizzes.submit') }}
                        </button>
                    </div>
                </form>
            @endif

            @if(!$quiz->is_public_anonymous_pool_mode && isset($canRegisterStudents) && !$canRegisterStudents)
                <form action="{{ route('quota_requests.store') }}" method="POST" class="mt-3">
                    @csrf
                    <input type="hidden" name="resource_type" value="students">
                    <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
                    <button type="submit" class="btn dashboard-btn dashboard-btn--ghost">
                        <i class="fas fa-envelope me-2"></i>{{ __('quizzes.request_more_students_button') }}
                    </button>
                </form>
            @endif
        </section>

        @if ($quiz->allow_resume && !$quiz->is_anonymous_bulk_mode)
            <section class="dashboard-section-card">
                <div class="dashboard-page-header">
                    <div>
                        <span class="dashboard-section-card__eyebrow">
                            <i class="fas fa-file-csv"></i>
                            {{ __('quizzes.import_csv') }}
                        </span>
                        <h2 class="dashboard-page-header__title">{{ __('quizzes.import_csv') }}</h2>
                    </div>
                </div>

                <form
                    action="{{ route('quiz_attempts.import_students', $quiz) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="students-import-form"
                    class="dashboard-form-stack"
                    data-student-import-form
                    data-max-lines="30"
                    data-max-attempts="5"
                    data-expected-header="student_name,student_code,max_attempts"
                    data-empty-file-message="{{ __('ui.csv_empty_file') }}"
                    data-read-error-message="{{ __('ui.csv_read_error') }}"
                    data-too-many-rows-message="{{ __('controllers.csv_too_many_rows') }}"
                    data-invalid-headers-message="{{ __('controllers.invalid_csv_headers') }}"
                    data-missing-fields-message="{{ __('ui.csv_missing_fields') }}"
                    data-invalid-code-message="{{ __('ui.csv_invalid_code') }}"
                    data-invalid-attempts-message="{{ __('ui.csv_invalid_attempts') }}"
                    data-reserved-code-message="{{ __('ui.csv_reserved_guest_code') }}"
                >
                    @csrf
                    <div class="dashboard-form-group">
                        <label for="students_csv" class="dashboard-form-label">
                            <i class="fas fa-upload text-muted"></i>{{ __('quizzes.upload_csv_file') }}
                        </label>
                        <input type="file" name="students_csv" id="students_csv" accept=".csv" class="form-control dashboard-form-control" data-student-import-input required>
                        <div class="dashboard-form-help">
                            {{ __('quizzes.csv_hint') }}<br>
                            <span class="dashboard-code-inline">student_name,student_code,max_attempts</span>
                        </div>
                        <div class="dashboard-form-help">
                            <a href="{{ asset('storage/docs/students_template.csv') }}" target="_blank" rel="noopener" class="dashboard-inline-link">
                                <i class="fas fa-file-csv me-1"></i>{{ __('quizzes.download_students_csv_template') }}
                            </a>
                        </div>
                    </div>

                    <div class="dashboard-form-actions dashboard-form-actions--end">
                        <button type="submit" class="btn dashboard-btn dashboard-btn--primary" @if(isset($canRegisterStudents) && !$canRegisterStudents) disabled @endif>
                            <i class="fas fa-upload me-2"></i>{{ __('quizzes.upload') }}
                        </button>
                    </div>
                </form>
            </section>
        @endif
    </div>
</div>
@endsection
