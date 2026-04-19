<?php

/**
 * QuizController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizOwnership;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuizController extends Controller
{
    use AuthorizesQuizOwnership;

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function exampleQuizzesQuery()
    {
        return Quiz::query()
            ->where('is_system_example', true)
            ->with('category')
            ->orderBy('title');
    }

    private function duplicateStoredImage(?string $sourcePath, string $directory): ?string
    {
        if (! is_string($sourcePath) || $sourcePath === '' || ! Storage::disk('public')->exists($sourcePath)) {
            return null;
        }

        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = (string) Str::uuid();
        $destination = trim($directory, '/').'/'.$filename.($extension !== '' ? '.'.$extension : '');

        Storage::disk('public')->copy($sourcePath, $destination);

        return $destination;
    }

    private function authorizeQuizDuplicationSource(Quiz $quiz): void
    {
        $user = $this->currentUser();

        if (! $user) {
            abort(403, 'Not authenticated.');
        }

        if ($user->isAdmin() || $quiz->creator_id === $user->id || $quiz->isSystemExample()) {
            return;
        }

        abort(403, 'You are not allowed to duplicate this quiz.');
    }

    private function updateLockedQuizStatusOnly(Request $request, Quiz $quiz): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:active,inactive',
            'is_certificate_verification_enabled' => 'nullable|boolean',
            'is_second_screen_enabled' => 'nullable|boolean',
            'notify_creator_on_pass' => 'nullable|boolean',
        ]);

        $data = [
            'status' => $validated['status'],
            'notify_creator_on_pass' => $request->boolean('notify_creator_on_pass'),
        ];

        if ($this->currentUserCanManageSpecialModes()) {
            $data['is_certificate_verification_enabled'] = $request->boolean('is_certificate_verification_enabled');
            $data['is_second_screen_enabled'] = $request->boolean('is_second_screen_enabled');
        }

        $quiz->update($data);

        return redirect()
            ->route('quizzes.index')
            ->with('success', __('controllers.quiz_updated'));
    }

    private function currentUserCanManageSpecialModes(): bool
    {
        return $this->currentUser()?->isAdmin() ?? false;
    }

    private function availableTemplatesQuery(?User $user)
    {
        $query = QuizTemplate::query();

        if (! $user || ! $user->isAdmin()) {
            $query->where(function ($builder) use ($user) {
                $builder->where('is_common', true);

                if ($user) {
                    $builder->orWhereHas('users', function ($assigned) use ($user) {
                        $assigned->where('users.id', $user->id);
                    });
                }
            });
        }

        return $query;
    }

    private function builtInTemplateViewsExist(?string $templateCode): bool
    {
        if (! is_string($templateCode) || trim($templateCode) === '') {
            return false;
        }

        foreach (['start', 'student', 'question', 'result'] as $screen) {
            if (! view()->exists('quiz.templates.'.$templateCode.'.'.$screen)) {
                return false;
            }
        }

        return true;
    }

    private function availableTemplatesForUser(?User $user)
    {
        $databaseTemplates = $this->availableTemplatesQuery($user)
            ->orderBy('name')
            ->get(['code', 'name']);

        $existingCodes = $databaseTemplates
            ->pluck('code')
            ->filter()
            ->all();

        $builtInTemplates = collect(glob(resource_path('views/quiz/templates/*'), GLOB_ONLYDIR) ?: [])
            ->map(fn (string $path) => basename($path))
            ->filter(fn (string $code) => $this->builtInTemplateViewsExist($code))
            ->reject(fn (string $code) => in_array($code, $existingCodes, true))
            ->map(fn (string $code) => (object) [
                'code' => $code,
                'name' => Str::headline(str_replace('_', ' ', $code)),
            ]);

        return $databaseTemplates
            ->concat($builtInTemplates)
            ->sortBy('name')
            ->values();
    }

    private function userCanUseTemplateCode(?string $templateCode, ?User $user): bool
    {
        if (! is_string($templateCode) || trim($templateCode) === '') {
            return false;
        }

        if ($this->builtInTemplateViewsExist($templateCode)) {
            return true;
        }

        return $this->availableTemplatesQuery($user)
            ->where('code', $templateCode)
            ->exists();
    }

    private function creatorCanCreateQuiz(): bool
    {
        $user = $this->currentUser();

        if (! $user || $user->isAdmin()) {
            return true;
        }

        return $user->quizzes()->count() < $user->max_quizzes;
    }

    /**
     * Show a paginated list of quizzes.
     * - Teachers see only their own quizzes.
     * - Admins see all quizzes.
     */
    public function index(): View
    {
        $user = $this->currentUser();

        if (! $user) {
            abort(403, 'Not authenticated.');
        }

        $quizzesQuery = Quiz::query()
            ->where('is_system_example', false)
            ->with('category');

        if ($user->role === 'teacher') {
            $quizzesQuery->where('creator_id', $user->id);
        }

        $quizzes = $quizzesQuery->latest()->paginate(10);
        $exampleQuizzes = $this->exampleQuizzesQuery()->get();

        $canCreateQuiz = $this->creatorCanCreateQuiz();

        return view('quizzes.index', compact('quizzes', 'exampleQuizzes', 'canCreateQuiz'));
    }

    /**
     * Show the form to create a new quiz.
     */
    public function create(): View|RedirectResponse
    {
        if (! $this->creatorCanCreateQuiz()) {
            return redirect()
                ->route('quizzes.index')
                ->with('error', __('controllers.quiz_limit_reached'));
        }

        $categories = Category::orderBy('name')->get();
        $templates = $this->availableTemplatesForUser($this->currentUser());

        return view('quizzes.create', compact('categories', 'templates'));
    }

    /**
     * Show the form to edit an existing quiz.
     */
    public function edit(Quiz $quiz): View
    {
        $this->authorizeQuizAccess($quiz);

        $categories = Category::orderBy('name')->get();
        $questionCount = $quiz->questions()->count();
        $templates = $this->availableTemplatesForUser($this->currentUser());
        $isContentLocked = $quiz->hasLockedContent();

        return view('quizzes.edit', compact('quiz', 'categories', 'questionCount', 'templates', 'isContentLocked'));
    }

    /**
     * Store a newly created quiz in the database.
     */
    public function store(Request $request): RedirectResponse
    {
        if (! $this->creatorCanCreateQuiz()) {
            return redirect()
                ->route('quizzes.index')
                ->with('error', __('controllers.quiz_limit_reached'));
        }

        $request->merge([
            'is_random_order' => $request->boolean('is_random_order'),
            'is_random_answers_order' => $request->boolean('is_random_answers_order'),
            'show_answer_numbering' => $request->boolean('show_answer_numbering'),
            'allow_guest' => $request->boolean('allow_guest'),
            'has_timer' => $request->boolean('has_timer'),
            'allow_resume' => $request->boolean('allow_resume'),
            'is_learning_mode' => $request->boolean('is_learning_mode'),
            'is_certificate_verification_enabled' => $request->boolean('is_certificate_verification_enabled'),
            'is_second_screen_enabled' => $request->boolean('is_second_screen_enabled'),
            'notify_creator_on_pass' => $request->boolean('notify_creator_on_pass', true),
            'is_public' => $request->boolean('is_public'),
            'is_anonymous_bulk_mode' => $request->boolean('is_anonymous_bulk_mode'),
            'is_public_anonymous_pool_mode' => $request->boolean('is_public_anonymous_pool_mode'),
            'student_access_policy' => (string) $request->input('student_access_policy', Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS),
        ]);

        $request->validate([
            'title' => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'time_limit' => 'required|integer|min:1',
            'pass_percentage' => 'required|integer|min:0|max:100',
            'question_view' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->userCanUseTemplateCode((string) $value, $this->currentUser())) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'is_random_order' => 'boolean',
            'is_random_answers_order' => 'boolean',
            'show_answer_numbering' => 'boolean',
            'allow_guest' => 'boolean',
            'has_timer' => 'boolean',
            'allow_resume' => 'boolean',
            'is_learning_mode' => 'boolean',
            'is_certificate_verification_enabled' => 'boolean',
            'is_second_screen_enabled' => 'boolean',
            'notify_creator_on_pass' => 'boolean',
            'is_anonymous_bulk_mode' => 'boolean',
            'is_public_anonymous_pool_mode' => 'boolean',
            'anonymous_pool_capacity' => 'nullable|integer|min:1|max:9999',
            'student_access_policy' => ['required', 'string', Rule::in(Quiz::studentAccessPolicies())],
            'status' => 'required|in:active,inactive',
            'language' => 'required|in:el,en,auto',
            'image' => 'nullable|image|max:150',
        ], [
            'image.max' => __('controllers.quiz_image_max'),
        ]);

        $canManageSpecialModes = $this->currentUserCanManageSpecialModes();
        $isAnonymousBulkMode = $canManageSpecialModes ? $request->boolean('is_anonymous_bulk_mode') : false;
        $isPublicAnonymousPoolMode = $canManageSpecialModes ? $request->boolean('is_public_anonymous_pool_mode') : false;
        $isCertificateVerificationEnabled = $canManageSpecialModes
            ? $request->boolean('is_certificate_verification_enabled')
            : false;
        $isSecondScreenEnabled = $canManageSpecialModes
            ? $request->boolean('is_second_screen_enabled')
            : false;

        if ($isAnonymousBulkMode && $isPublicAnonymousPoolMode) {
            return back()->withInput()->withErrors([
                'is_public_anonymous_pool_mode' => __('controllers.special_modes_conflict'),
            ]);
        }

        if ($request->boolean('is_learning_mode') && ($isAnonymousBulkMode || $isPublicAnonymousPoolMode)) {
            return back()->withInput()->withErrors([
                'is_learning_mode' => __('controllers.learning_mode_special_modes_conflict'),
            ]);
        }

        if ($isSecondScreenEnabled && ($request->boolean('is_learning_mode') || $isAnonymousBulkMode || $isPublicAnonymousPoolMode)) {
            return back()->withInput()->withErrors([
                'is_second_screen_enabled' => __('controllers.second_screen_conflict'),
            ]);
        }

        $anonymousPoolCapacity = $isPublicAnonymousPoolMode
            ? max(1, (int) $request->integer('anonymous_pool_capacity', 100))
            : null;

        if ($isAnonymousBulkMode) {
            $allowGuest = false;
            $isPublic = false;
            $allowResume = $request->boolean('allow_resume');
        } elseif ($isPublicAnonymousPoolMode) {
            $allowGuest = false;
            $isPublic = true;
            $allowResume = false;
        } else {
            $allowGuest = $request->boolean('allow_guest');
            $isPublic = $allowGuest ? $request->boolean('is_public') : false;
            $allowResume = $request->boolean('allow_resume');
        }

        do {
            $quizCode = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Quiz::where('quiz_code', $quizCode)->exists());

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'creator_id' => Auth::id(),
            'quiz_code' => $quizCode,
            'time_limit' => $request->time_limit * 60,
            'is_random_order' => $request->is_random_order,
            'is_random_answers_order' => $request->is_random_answers_order,
            'show_answer_numbering' => $request->show_answer_numbering,
            'allow_guest' => $allowGuest,
            'has_timer' => $request->has_timer,
            'allow_resume' => $allowResume,
            'is_learning_mode' => $request->boolean('is_learning_mode'),
            'is_certificate_verification_enabled' => $isCertificateVerificationEnabled,
            'is_second_screen_enabled' => $isSecondScreenEnabled,
            'notify_creator_on_pass' => $request->boolean('notify_creator_on_pass', true),
            'pass_percentage' => $request->pass_percentage,
            'question_view' => $request->question_view,
            'status' => $request->status,
            'is_public' => $isPublic,
            'is_anonymous_bulk_mode' => $isAnonymousBulkMode,
            'is_public_anonymous_pool_mode' => $isPublicAnonymousPoolMode,
            'anonymous_pool_capacity' => $anonymousPoolCapacity,
            'student_access_policy' => (string) $request->input('student_access_policy', Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS),
            'public_token' => null,
            'public_token_hash' => (($allowGuest && $isPublic) || $isPublicAnonymousPoolMode)
                ? Quiz::generateLinkTokenHash()
                : null,
            'language' => $request->language,
        ];

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('quizzes_images', 'public');
        }

        Quiz::create($data);

        return redirect()
            ->route('quizzes.index')
            ->with('success', __('controllers.quiz_created'));
    }

    /**
     * Update an existing quiz.
     */
    public function update(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);

        if ($quiz->hasLockedContent()) {
            return $this->updateLockedQuizStatusOnly($request, $quiz);
        }

        $request->merge([
            'is_random_order' => $request->boolean('is_random_order'),
            'is_random_answers_order' => $request->boolean('is_random_answers_order'),
            'show_answer_numbering' => $request->boolean('show_answer_numbering'),
            'allow_guest' => $request->boolean('allow_guest'),
            'has_timer' => $request->boolean('has_timer'),
            'allow_resume' => $request->boolean('allow_resume'),
            'is_learning_mode' => $request->boolean('is_learning_mode'),
            'is_certificate_verification_enabled' => $request->boolean('is_certificate_verification_enabled'),
            'is_second_screen_enabled' => $request->boolean('is_second_screen_enabled'),
            'notify_creator_on_pass' => $request->boolean('notify_creator_on_pass', $quiz->shouldNotifyCreatorOnPass()),
            'is_public' => $request->boolean('is_public'),
            'is_anonymous_bulk_mode' => $request->boolean('is_anonymous_bulk_mode'),
            'is_public_anonymous_pool_mode' => $request->boolean('is_public_anonymous_pool_mode'),
            'student_access_policy' => (string) $request->input('student_access_policy', $quiz->studentAccessPolicy()),
        ]);

        $rules = [
            'title' => 'required|string|max:80',
            'description' => 'nullable|string|max:200',
            'category_id' => 'required|exists:categories,id',
            'time_limit' => 'required|integer|min:1',
            'pass_percentage' => 'required|integer|min:0|max:100',
            'question_view' => [
                'required',
                'string',
                'max:50',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->userCanUseTemplateCode((string) $value, $this->currentUser())) {
                        $fail(__('validation.exists', ['attribute' => $attribute]));
                    }
                },
            ],
            'is_random_order' => 'boolean',
            'is_random_answers_order' => 'boolean',
            'show_answer_numbering' => 'boolean',
            'allow_guest' => 'boolean',
            'has_timer' => 'boolean',
            'allow_resume' => 'boolean',
            'is_learning_mode' => 'boolean',
            'is_certificate_verification_enabled' => 'boolean',
            'is_second_screen_enabled' => 'boolean',
            'notify_creator_on_pass' => 'boolean',
            'is_anonymous_bulk_mode' => 'boolean',
            'is_public_anonymous_pool_mode' => 'boolean',
            'anonymous_pool_capacity' => 'nullable|integer|min:1|max:9999',
            'student_access_policy' => ['required', 'string', Rule::in(Quiz::studentAccessPolicies())],
            'status' => 'required|in:active,inactive',
            'language' => 'required|in:el,en,auto',
            'image' => 'nullable|image|max:150',
            'delete_image' => 'nullable|boolean',
        ];

        if ($request->is_random_order) {
            $rules['questions_limit'] = ['nullable', 'integer', 'min:1'];
        }

        $request->validate($rules, [
            'image.max' => __('controllers.quiz_image_max'),
        ]);

        $canManageSpecialModes = $this->currentUserCanManageSpecialModes();
        $isAnonymousBulkMode = $canManageSpecialModes
            ? $request->boolean('is_anonymous_bulk_mode')
            : (bool) $quiz->is_anonymous_bulk_mode;
        $isPublicAnonymousPoolMode = $canManageSpecialModes
            ? $request->boolean('is_public_anonymous_pool_mode')
            : (bool) $quiz->is_public_anonymous_pool_mode;
        $isCertificateVerificationEnabled = $canManageSpecialModes
            ? $request->boolean('is_certificate_verification_enabled')
            : $quiz->usesCertificateVerification();
        $isSecondScreenEnabled = $canManageSpecialModes
            ? $request->boolean('is_second_screen_enabled')
            : $quiz->usesSecondScreenMode();

        if ($isAnonymousBulkMode && $isPublicAnonymousPoolMode) {
            return back()->withInput()->withErrors([
                'is_public_anonymous_pool_mode' => __('controllers.special_modes_conflict'),
            ]);
        }

        if ($request->boolean('is_learning_mode') && ($isAnonymousBulkMode || $isPublicAnonymousPoolMode)) {
            return back()->withInput()->withErrors([
                'is_learning_mode' => __('controllers.learning_mode_special_modes_conflict'),
            ]);
        }

        if ($isSecondScreenEnabled && ($request->boolean('is_learning_mode') || $isAnonymousBulkMode || $isPublicAnonymousPoolMode)) {
            return back()->withInput()->withErrors([
                'is_second_screen_enabled' => __('controllers.second_screen_conflict'),
            ]);
        }

        $anonymousPoolCapacity = $isPublicAnonymousPoolMode
            ? (
                $canManageSpecialModes
                    ? max(1, (int) $request->integer('anonymous_pool_capacity', (int) ($quiz->anonymous_pool_capacity ?: 100)))
                    : $quiz->anonymous_pool_capacity
            )
            : null;

        if ($isAnonymousBulkMode) {
            $allowGuest = false;
            $isPublic = false;
            $allowResume = $request->boolean('allow_resume');
        } elseif ($isPublicAnonymousPoolMode) {
            $allowGuest = false;
            $isPublic = true;
            $allowResume = false;
        } else {
            $allowGuest = $request->boolean('allow_guest');
            $isPublic = $allowGuest ? $request->boolean('is_public') : false;
            $allowResume = $request->boolean('allow_resume');
        }

        $questionsLimit = $request->is_random_order && $request->filled('questions_limit')
            ? (int) $request->questions_limit
            : null;

        if ($questionsLimit !== null && $questionsLimit > $quiz->questions()->count()) {
            return redirect()->back()->withInput()->withErrors([
                'questions_limit' => __('controllers.questions_limit_exceeded'),
            ]);
        }

        $data = [
            'title' => $request->title,
            'description' => $request->description,
            'category_id' => $request->category_id,
            'time_limit' => $request->time_limit * 60,
            'is_random_order' => $request->is_random_order,
            'is_random_answers_order' => $request->is_random_answers_order,
            'show_answer_numbering' => $request->show_answer_numbering,
            'allow_guest' => $allowGuest,
            'has_timer' => $request->has_timer,
            'allow_resume' => $allowResume,
            'is_learning_mode' => $request->boolean('is_learning_mode'),
            'is_certificate_verification_enabled' => $isCertificateVerificationEnabled,
            'is_second_screen_enabled' => $isSecondScreenEnabled,
            'notify_creator_on_pass' => $request->boolean('notify_creator_on_pass', $quiz->shouldNotifyCreatorOnPass()),
            'pass_percentage' => $request->pass_percentage,
            'question_view' => $request->question_view,
            'status' => $request->status,
            'questions_limit' => $questionsLimit,
            'is_public' => $isPublic,
            'is_anonymous_bulk_mode' => $isAnonymousBulkMode,
            'is_public_anonymous_pool_mode' => $isPublicAnonymousPoolMode,
            'anonymous_pool_capacity' => $anonymousPoolCapacity,
            'student_access_policy' => (string) $request->input('student_access_policy', $quiz->studentAccessPolicy()),
            'public_token' => null,
            'public_token_hash' => ($allowGuest && $isPublic) || $isPublicAnonymousPoolMode
                ? ($quiz->public_token_hash ?: Quiz::generateLinkTokenHash())
                : null,
            'language' => $request->language,
        ];

        if ($request->boolean('delete_image') && $quiz->image) {
            if (Storage::disk('public')->exists($quiz->image)) {
                Storage::disk('public')->delete($quiz->image);
            }
            $data['image'] = null;
        }

        if ($request->hasFile('image')) {
            if ($quiz->image && Storage::disk('public')->exists($quiz->image)) {
                Storage::disk('public')->delete($quiz->image);
            }
            $data['image'] = $request->file('image')->store('quizzes_images', 'public');
        }

        $quiz->update($data);

        return redirect()
            ->route('quizzes.index')
            ->with('success', __('controllers.quiz_updated'));
    }

    /**
     * Delete the given quiz and redirect to quiz list.
     */
    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);

        $quiz->delete();

        return redirect()
            ->route('quizzes.index')
            ->with('success', __('controllers.quiz_deleted'));
    }

    /**
     * Export all quiz questions and answers as a printable PDF file.
     */
    public function exportPrintablePdf(Quiz $quiz): Response|BinaryFileResponse
    {
        $this->authorizeQuizReadAccess($quiz);

        App::setLocale($quiz->language ?? config('app.locale'));

        $questions = $quiz->questions()->with('answers')->get();

        $pdf = Pdf::loadView('quizzes.printable_pdf', [
            'quiz' => $quiz,
            'questions' => $questions,
        ])->setPaper('A4');

        $this->addCenteredPdfPageFooter($pdf, __('pdfexp.page_footer'));

        return $pdf->download('quiz_printable_'.$quiz->id.'.pdf');
    }

    /**
     * Duplicate an existing quiz into the authenticated user's own collection.
     */
    public function duplicate(Quiz $quiz): RedirectResponse
    {
        $this->authorizeQuizDuplicationSource($quiz);

        if (! $this->creatorCanCreateQuiz()) {
            return redirect()
                ->route('quizzes.index')
                ->with('error', __('controllers.quiz_limit_reached'));
        }

        $owner = $this->currentUser();

        if (! $owner) {
            abort(403, 'Not authenticated.');
        }

        $quiz->load(['questions.answers']);

        $newQuiz = DB::transaction(function () use ($quiz, $owner): Quiz {
            do {
                $quizCode = str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
            } while (Quiz::where('quiz_code', $quizCode)->exists());

            $questionView = $this->userCanUseTemplateCode($quiz->question_view, $owner)
                ? $quiz->question_view
                : 'default';

            $copiedQuiz = Quiz::create([
                'title' => $quiz->title,
                'description' => $quiz->description,
                'category_id' => $quiz->category_id,
                'creator_id' => $owner->id,
                'quiz_code' => $quizCode,
                'max_attempts' => $quiz->max_attempts,
                'time_limit' => $quiz->time_limit,
                'is_random_order' => (bool) $quiz->is_random_order,
                'is_random_answers_order' => (bool) $quiz->is_random_answers_order,
                'show_answer_numbering' => (bool) $quiz->show_answer_numbering,
                'allow_guest' => (bool) $quiz->allow_guest,
                'has_timer' => (bool) $quiz->has_timer,
                'allow_resume' => (bool) $quiz->allow_resume,
                'is_learning_mode' => (bool) $quiz->is_learning_mode,
                'is_certificate_verification_enabled' => $owner->isAdmin()
                    ? (bool) $quiz->is_certificate_verification_enabled
                    : false,
                'is_second_screen_enabled' => $owner->isAdmin()
                    ? (bool) $quiz->is_second_screen_enabled
                    : false,
                'notify_creator_on_pass' => (bool) $quiz->notify_creator_on_pass,
                'pass_percentage' => $quiz->pass_percentage,
                'question_view' => $questionView,
                'status' => 'inactive',
                'questions_limit' => $quiz->questions_limit,
                'public_token' => null,
                'public_token_hash' => (($quiz->allow_guest && $quiz->is_public) || $quiz->is_public_anonymous_pool_mode)
                    ? Quiz::generateLinkTokenHash()
                    : null,
                'is_public' => (bool) $quiz->is_public,
                'is_anonymous_bulk_mode' => $owner->isAdmin() ? (bool) $quiz->is_anonymous_bulk_mode : false,
                'is_public_anonymous_pool_mode' => $owner->isAdmin() ? (bool) $quiz->is_public_anonymous_pool_mode : false,
                'anonymous_pool_capacity' => $owner->isAdmin() ? $quiz->anonymous_pool_capacity : null,
                'student_access_policy' => $quiz->studentAccessPolicy(),
                'language' => $quiz->language ?: 'auto',
                'image' => $this->duplicateStoredImage($quiz->image, 'quizzes_images'),
                'is_system_example' => false,
                'system_key' => null,
            ]);

            foreach ($quiz->questions as $question) {
                $copiedQuestion = $copiedQuiz->questions()->create([
                    'text' => $question->text,
                    'image' => $this->duplicateStoredImage($question->image, 'questions_images'),
                    'correct_answers_count' => $question->correct_answers_count,
                    'order' => $question->order,
                ]);

                foreach ($question->answers as $answer) {
                    $copiedQuestion->answers()->create([
                        'text' => $answer->text,
                        'is_correct' => (bool) $answer->is_correct,
                    ]);
                }
            }

            return $copiedQuiz;
        });

        return redirect()
            ->route('quizzes.edit', $newQuiz)
            ->with('success', __('controllers.quiz_duplicated'));
    }
}
