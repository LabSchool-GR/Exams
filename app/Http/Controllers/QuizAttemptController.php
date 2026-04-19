<?php

/**
 * QuizAttemptController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizOwnership;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use App\Services\AttemptLifecycleService;
use App\Support\StudentNameBlindIndex;
use App\Exports\QuizAttemptsExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QuizAttemptController extends Controller
{
	use AuthorizesQuizOwnership {
		authorizeQuizAccess as protected authorizeOwnedQuizAccess;
	}

	private const MAX_REGISTERED_STUDENT_ATTEMPTS = 5;

	public function __construct(
		private readonly AttemptLifecycleService $attemptLifecycle
	) {
	}

	/**
	 * Enforce object-level access to quiz-owned data.
	 */
	protected function authorizeQuizAccess(Quiz $quiz): void
	{
		$user = auth()->user();
		if (!$user) {
			abort(403, 'Not authenticated.');
		}
		// Admins may access every quiz-owned resource without creator checks.
		if ($user->role === 'admin') {
			return;
		}
		// All non-admin access is scoped to the teacher who created the quiz.
		if ($quiz->creator_id !== $user->id) {
			abort(403, __('controllers.quiz_access_forbidden'));
		}
	}

	/**
	 * Resolve the parent quiz from an attempt and fail safely if the relation is missing.
	 */
	private function resolveQuizFromAttempt(QuizAttempt $attempt): Quiz
	{
		$quiz = $attempt->quiz;

		if (!$quiz) {
			abort(404);
		}

		return $quiz;
	}

	/**
	 * Allow force-submit only for the attempt currently owned by the active quiz session.
	 */
	private function sessionOwnsAttempt(QuizAttempt $attempt): bool
	{
		return (int) Session::get('attempt_id') === (int) $attempt->id
			&& (int) Session::get('quiz_id') === (int) $attempt->quiz_id;
	}

	private function canRegisterMoreStudents(Quiz $quiz): bool
	{
		$remainingCapacity = $this->remainingStudentCapacity($quiz);

		return $remainingCapacity === null || $remainingCapacity > 0;
	}

	private function remainingStudentCapacity(Quiz $quiz): ?int
	{
		$user = auth()->user();

		if (!$user || $user->isAdmin()) {
			return null;
		}

		return max(0, (int) $user->max_students_per_quiz - QuizStudent::where('quiz_id', $quiz->id)->count());
	}

	private function anonymousStudentDisplayName(): string
	{
		return __('controllers.anonymous_student_name');
	}

	private function nextAvailableStudentCodes(Quiz $quiz, int $count): array
	{
		$existingCodes = QuizStudent::query()
			->where('quiz_id', $quiz->id)
			->pluck('student_code')
			->all();

		$usedCodes = array_fill_keys($existingCodes, true);
		$availableCodes = [];

		for ($number = 1; $number <= 9999 && count($availableCodes) < $count; $number++) {
			$code = str_pad((string) $number, 4, '0', STR_PAD_LEFT);

			if ($code === '0000' || isset($usedCodes[$code])) {
				continue;
			}

			$availableCodes[] = $code;
			$usedCodes[$code] = true;
		}

		return $availableCodes;
	}

	/**
	 * Display a paginated list of quiz attempts with optional student name search.
	 */
	public function index(Request $request, Quiz $quiz): View
	{
		$this->authorizeQuizAccess($quiz);
		$perPage = max(1, (int) $request->integer('per_page', 10));
		$search = trim((string) $request->get('search', ''));
		$attempts = $quiz->attempts()
			->with('quiz')
			->when($search !== '', function ($query) use ($search) {
				$hashes = StudentNameBlindIndex::queryHashes($search);

				$query->where(function ($nested) use ($search, $hashes) {
					if ($hashes !== []) {
						$nested->where(function ($nameQuery) use ($hashes) {
							foreach ($hashes as $hash) {
								$nameQuery->where('student_name_blind_index', 'like', '%|' . $hash . '|%');
							}
						});
					}

					$codePattern = '%' . $search . '%';
					if ($hashes !== []) {
						$nested->orWhere('student_code', 'like', $codePattern);
					} else {
						$nested->where('student_code', 'like', $codePattern);
					}
				});
			})
			->orderByDesc('created_at')
			->paginate($perPage)
			->appends([
				'per_page' => $perPage,
				'search' => $search,
			]);

		return view('quiz_attempts.index', compact('quiz', 'attempts', 'search', 'perPage'));
	}
	/**
	 * Show form to create a new quiz attempt manually.
	 */
	public function create(Quiz $quiz): View
	{
		$this->authorizeQuizAccess($quiz);

		return view('quiz_attempts.create', compact('quiz'));
	}

	/**
	 * Attempts are read-only from the dashboard to preserve exam integrity.
	 */
	public function submit(Quiz $quiz, QuizAttempt $quizAttempt): RedirectResponse
	{
		$this->authorizeQuizAccess($quiz);
		$this->ensureAttemptBelongsToQuiz($quiz, $quizAttempt);

		return back()->with('error', __('controllers.attempt_read_only'));
	}

	/**
	 * Display all registered students and their grouped attempts for a quiz.
	 */
	public function registerStudents(Quiz $quiz): View
	{
		$this->authorizeQuizAccess($quiz);

		$students = QuizStudent::query()
			->where('quiz_id', $quiz->id)
			->orderBy('student_code')
			->orderBy('id')
			->get();

		$attemptsGrouped = QuizAttempt::where('quiz_id', $quiz->id)
			->get()
			->groupBy(function (QuizAttempt $attempt) {
				// Prefer the relational key for new data, but keep a legacy fallback.
				return $attempt->quiz_student_id
					? 'student:' . $attempt->quiz_student_id
					: 'code:' . $attempt->student_code;
			});

		$canRegisterStudents = $this->canRegisterMoreStudents($quiz);
		$studentLimit = auth()->user()?->max_students_per_quiz;
		$publicAnonymousPoolCompletedCount = QuizAttempt::query()
			->where('quiz_id', $quiz->id)
			->whereNotNull('submitted_at')
			->whereHas('student', function ($query) {
				$query->where('is_anonymous', true);
			})
			->count();

        $displaySessionsByStudentId = QuizDisplaySession::query()
            ->where('quiz_id', $quiz->id)
            ->whereIn('status', [
                QuizDisplaySession::STATUS_WAITING,
                QuizDisplaySession::STATUS_ACTIVE,
            ])
            ->with('attempt')
            ->latest('id')
            ->get()
            ->filter(fn (QuizDisplaySession $session) => $session->attempt?->isInProgress())
            ->unique('quiz_student_id')
            ->keyBy('quiz_student_id');

		return view('quiz_attempts.register_students', compact('quiz', 'students', 'attemptsGrouped', 'canRegisterStudents', 'studentLimit', 'publicAnonymousPoolCompletedCount', 'displaySessionsByStudentId'));
	}

    /**
	 * Register a student for the quiz.
	 */
	public function storeStudent(Request $request, Quiz $quiz): RedirectResponse
	{
		$this->authorizeQuizAccess($quiz);

		if ($quiz->is_anonymous_bulk_mode) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.anonymous_bulk_mode_manual_disabled'));
		}

		if ($quiz->is_public_anonymous_pool_mode) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.public_anonymous_pool_manual_disabled'));
		}

		if (!$this->canRegisterMoreStudents($quiz)) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.student_limit_reached'));
		}

		$request->validate([
			'student_code' => 'required|string|size:4',
			'student_name' => 'required|string|max:255',
			'max_attempts' => 'required|integer|min:1|max:' . self::MAX_REGISTERED_STUDENT_ATTEMPTS,
		]);

		// Handle guest user
		if ($request->student_code === '0000') {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('success', __('controllers.guest_info_stored'));
		}

		$alreadyExists = DB::table('quiz_students')
			->where('quiz_id', $quiz->id)
			->where('student_code', $request->student_code)
			->exists();

		if ($alreadyExists) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.student_already_exists'));
		}

		QuizStudent::create([
			'quiz_id' => $quiz->id,
			'student_code' => $request->student_code,
			'student_name' => $request->student_name,
			'max_attempts' => $request->max_attempts,
			'is_anonymous' => false,
			'access_token' => null,
			'access_token_hash' => QuizStudent::generateLinkTokenHash(),
		]);

		return redirect()->route('quiz_attempts.register_students', $quiz)
			->with('success', __('controllers.student_registered_successfully'));
	}

	public function storeAnonymousStudents(Request $request, Quiz $quiz): RedirectResponse
	{
		$this->authorizeQuizAccess($quiz);

		if (!$quiz->is_anonymous_bulk_mode) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.anonymous_bulk_mode_disabled'));
		}

		if (!$this->canRegisterMoreStudents($quiz)) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.student_limit_reached'));
		}

		$remainingCapacity = $this->remainingStudentCapacity($quiz);
		$requestedSlotsLimit = $remainingCapacity ?? 9999;

		$request->validate([
			'anonymous_slots_count' => 'required|integer|min:1|max:' . max(1, $requestedSlotsLimit),
			'anonymous_max_attempts' => 'required|integer|min:1|max:' . self::MAX_REGISTERED_STUDENT_ATTEMPTS,
		]);

		$slotCount = (int) $request->input('anonymous_slots_count');
		$maxAttempts = (int) $request->input('anonymous_max_attempts');
		$codes = $this->nextAvailableStudentCodes($quiz, $slotCount);

		if (count($codes) !== $slotCount) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.anonymous_slots_unavailable'));
		}

		DB::transaction(function () use ($quiz, $codes, $maxAttempts): void {
			foreach ($codes as $code) {
				QuizStudent::create([
					'quiz_id' => $quiz->id,
					'student_code' => $code,
					'student_name' => $this->anonymousStudentDisplayName(),
					'max_attempts' => $maxAttempts,
					'is_anonymous' => true,
					'access_token' => null,
					'access_token_hash' => QuizStudent::generateLinkTokenHash(),
				]);
			}
		});

		return redirect()->route('quiz_attempts.register_students', $quiz)
			->with('success', __('controllers.anonymous_slots_created', ['count' => $slotCount]));
	}

	/**
	 * Delete a student's attempts and registration from the quiz.
	 */
    public function destroyStudent(Quiz $quiz, QuizStudent $student): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        abort_unless($student->quiz_id === $quiz->id, 404);

        $studentCode = $student->student_code;
        $studentId = $student->id;

        $allAttempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where(function ($query) use ($studentId, $studentCode) {
                $query->where('quiz_student_id', $studentId)
                    ->orWhere(function ($legacyQuery) use ($studentCode, $studentId) {
                        $legacyQuery->whereNull('quiz_student_id')
                            ->where('student_code', $studentCode);
                    });
            })
            ->get();

        $deletedAttemptIds = $allAttempts->pluck('id')->all();

        foreach ($allAttempts as $attempt) {
            $attempt->answers()->delete();
            $attempt->delete();
        }

        DB::table('quiz_students')
            ->where('quiz_id', $quiz->id)
            ->when($studentId, function ($query, $studentId) {
                $query->where('id', $studentId);
            }, function ($query) use ($studentCode) {
                $query->where('student_code', $studentCode);
            })
            ->delete();

        if (in_array((int) Session::get('attempt_id'), $deletedAttemptIds, true)) {
            Session::forget([
                'attempt_id',
                'quiz_id',
                'question_order',
                'question_route_map',
                'quiz_route_quiz_id',
                'quiz_route_token',
                'answer_order_map',
                'current_question_index',
                'skipped_questions',
                'quiz_end_time',
                'guest_answers',
                'guest_answered',
                'student_code',
                'student_name',
            ]);
        }

		return redirect()->route('quiz_attempts.register_students', $quiz)
			->with('success', __('controllers.student_completely_deleted'));
	}

	/**
	 * Delete a quiz attempt and all its answers.
	 */
	public function destroy(Quiz $quiz, QuizAttempt $attempt): RedirectResponse
	{
		$this->authorizeQuizAccess($quiz);

		$this->ensureAttemptBelongsToQuiz($quiz, $attempt);

		$attempt->answers()->delete();
		$attempt->delete();

		return redirect()
			->route('quizzes.quiz_attempts.index', $quiz)
			->with('success', __('controllers.attempt_deleted_successfully'));
	}

	/**
	 * Import students from a CSV file for the given quiz.
	 * Only allowed if quiz has allow_resume enabled.
	 */
	public function importStudents(Request $request, Quiz $quiz): RedirectResponse
	{
		$this->authorizeQuizAccess($quiz);

		if ($quiz->is_anonymous_bulk_mode) {
			return back()->with('error', __('controllers.anonymous_bulk_mode_manual_disabled'));
		}

		if ($quiz->is_public_anonymous_pool_mode) {
			return back()->with('error', __('controllers.public_anonymous_pool_manual_disabled'));
		}

		$remainingCapacity = $this->remainingStudentCapacity($quiz);
		if ($remainingCapacity !== null && $remainingCapacity < 1) {
			return back()->with('error', __('controllers.student_limit_reached'));
		}

		// Reject if quiz is not resumable
		if (!$quiz->allow_resume) {
			return back()->with('error', __('controllers.resume_not_allowed_csv'));
		}

		// Validate uploaded file (CSV up to 1MB)
		$request->validate([
			'students_csv' => 'required|file|mimes:csv,txt|max:1024',
		]);

		$file = $request->file('students_csv');
		$rows = array_map('str_getcsv', file($file->getRealPath()));

		// Validate headers
		$header = array_map('trim', array_shift($rows));
		$expected = ['student_name', 'student_code', 'max_attempts'];
		if ($header !== $expected) {
			return back()->with('error', __('controllers.invalid_csv_headers'));
		}

		// Reject if more than 30 entries
		if (count($rows) > 30) {
			return back()->with('error', __('controllers.csv_too_many_rows'));
		}

		if ($remainingCapacity !== null) {
			if (count($rows) > $remainingCapacity) {
				return back()->with('error', __('controllers.student_import_limit_reached', [
					'remaining' => $remainingCapacity,
				]));
			}
		}

		$newStudents = [];
		$errors = [];
		$existingCodes = DB::table('quiz_students')
			->where('quiz_id', $quiz->id)
			->pluck('student_code')
			->toArray();

		foreach ($rows as $index => $row) {
			[$name, $code, $attempts] = array_map('trim', $row);

			// Row number for messages (1-based + header)
			$line = $index + 2;

			// Validate fields
			if (empty($name) || empty($code) || empty($attempts)) {
				$errors[] = "Line $line: Missing fields.";
				continue;
			}

			if (!preg_match('/^\d{4}$/', $code)) {
				$errors[] = "Line $line: Student code must be a 4-digit number.";
				continue;
			}

			if ($code === '0000') {
				$errors[] = "Line $line: Student code 0000 is reserved for guest access.";
				continue;
			}

			if (!is_numeric($attempts) || (int)$attempts < 1 || (int)$attempts > 5) {
				$errors[] = "Line $line: Attempts must be a number between 1 and 5.";
				continue;
			}

			if (in_array($code, $existingCodes)) {
				$errors[] = "Line $line: Duplicate student code '$code' already exists.";
				continue;
			}

			// Queue insertion
			$newStudents[] = [
				'quiz_id' => $quiz->id,
				'student_name' => $name,
				'student_code' => $code,
				'max_attempts' => (int)$attempts,
				'is_anonymous' => false,
				'access_token' => null,
				'access_token_hash' => QuizStudent::generateLinkTokenHash(),
			];

			// Avoid future duplicates within same import
			$existingCodes[] = $code;
		}

		// If errors found, abort and show all messages
		if (!empty($errors)) {
			return back()->with('error', implode("\n", $errors));
		}

		// Insert all valid rows
		foreach ($newStudents as $studentData) {
			QuizStudent::create($studentData);
		}

		return back()->with('success', __('controllers.students_imported_successfully'));
	}
	
	/**
	 * Generate and download the quiz results PDF for a student's attempt.
	 */
	public function downloadPdf(Quiz $quiz, QuizAttempt $quizAttempt): Response|BinaryFileResponse
	{
		// Signed links may bypass teacher authentication for public result downloads.
		if (!request()->hasValidSignature()) {
			$this->authorizeQuizAccess($quiz);
		}

		$this->ensureAttemptBelongsToQuiz($quiz, $quizAttempt);

		$quiz->load(['creator', 'questions.answers']);
		$quizAttempt->load(['answers.answer', 'answers.question']);

		App::setLocale($quiz->language ?? config('app.locale'));

		$groupedAnswers = $quizAttempt->answers->groupBy('question_id');
		$questionResults = [];
		$correctAnswersMap = [];

		$questionIds = $this->determineQuestionIds($quiz, $quizAttempt);
		$filteredQuestions = collect($questionIds)
			->map(fn (int $questionId) => $quiz->questions->firstWhere('id', $questionId))
			->filter();
		$totalQuestions = $filteredQuestions->count();
		$correctCount = 0;

		foreach ($filteredQuestions as $question) {
			$correctIds = $question->answers
				->where('is_correct', true)
				->pluck('id')
				->sort()
				->values()
				->all();

			$correctAnswersMap[$question->id] = $question->answers
				->where('is_correct', true)
				->pluck('text')
				->toArray();

			$studentAnswers = $groupedAnswers->get($question->id, collect());
			$studentIds = $studentAnswers
				->pluck('answer_id')
				->sort()
				->values()
				->all();

			$isCorrect = !empty($correctIds) && $correctIds === $studentIds;
			$questionResults[$question->id] = $isCorrect;

			if ($isCorrect) {
				$correctCount++;
			}
		}

		$scorePercentage = $quizAttempt->score !== null
			? (float) $quizAttempt->score
			: ($totalQuestions > 0 ? round(($correctCount / $totalQuestions) * 100, 2) : 0.0);

		$pdfData = [
			'quiz' => $quiz,
			'attempt' => $quizAttempt,
			'groupedAnswersByQuestion' => $groupedAnswers,
			'questionResults' => $questionResults,
			'correctAnswersMap' => $correctAnswersMap,
			'scorePercentage' => $scorePercentage,
		];

		$pdf = Pdf::loadView('quiz_attempts.result_pdf', $pdfData);

		$this->addCenteredPdfPageFooter($pdf, __('pdfexp.page_footer'));

		return $pdf->download(
			'quiz_results_' . $quizAttempt->student_code . '.pdf'
		);
	}

	
	/**
	 * Generate a PDF report of all registered students with basic info.
	 */
	public function studentsReportPdf(Quiz $quiz): \Symfony\Component\HttpFoundation\Response
	{
		$this->authorizeQuizAccess($quiz);

		// Load all registered students
		$students = QuizStudent::query()
			->where('quiz_id', $quiz->id)
			->orderBy('student_code')
			->orderBy('id')
			->get(['student_name', 'student_code', 'max_attempts']);

		// Prepare data (simplified)
		$reportData = $students->map(function ($student) {
			return [
				'name' => $student->student_name,
				'code' => $student->student_code,
				'max_attempts' => $student->max_attempts,
			];
		});

		$pdf = Pdf::loadView('quiz_attempts.students_report_pdf', [
			'quiz' => $quiz,
			'data' => $reportData,
		]);

		return $pdf->download('students_report_' . $quiz->id . '.pdf');
	}

	public function downloadAnonymousCardsPdf(Quiz $quiz): Response|BinaryFileResponse|RedirectResponse
	{
		$this->authorizeQuizAccess($quiz);

		if (!$quiz->is_anonymous_bulk_mode) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('controllers.anonymous_bulk_mode_disabled'));
		}

		App::setLocale($quiz->language ?? config('app.locale'));

		$students = QuizStudent::query()
			->where('quiz_id', $quiz->id)
			->where('student_code', '!=', '0000')
			->orderBy('student_code')
			->orderBy('id')
			->get();

		if ($students->isEmpty()) {
			return redirect()->route('quiz_attempts.register_students', $quiz)
				->with('error', __('quizzes.no_students'));
		}

		$cards = $students->map(function (QuizStudent $student) {
			$url = $student->accessLinkUrl();

			return [
				'student_code' => $student->student_code,
				'student_name' => $this->anonymousStudentDisplayName(),
				'max_attempts' => $student->max_attempts,
				'student_url' => $url,
				'qr_svg' => $url
					? base64_encode(QrCode::format('svg')->size(150)->generate($url))
					: null,
			];
		});

		$pdf = Pdf::loadView('quiz_attempts.anonymous_cards_pdf', [
			'quiz' => $quiz,
			'cards' => $cards,
		]);

		return $pdf->download('anonymous_cards_' . $quiz->id . '.pdf');
	}


	/**
	 * Determine the list of question IDs relevant for the current attempt.
	 */
	private function determineQuestionIds(Quiz $quiz, QuizAttempt $quizAttempt): array
	{
		$questionOrder = collect($quizAttempt->question_order ?? [])
			->filter(fn ($id) => is_numeric($id))
			->map(fn ($id) => (int) $id)
			->values()
			->all();

		if (!empty($questionOrder)) {
			return $questionOrder;
		}

		if ($quiz->is_random_order && $quiz->questions_limit) {
			return $quizAttempt->answers
				->pluck('question_id')
				->map(fn ($id) => (int) $id)
				->unique()
				->slice(0, $quiz->questions_limit)
				->values()
				->all();
		}

		return $quiz->questions
			->pluck('id')
			->map(fn ($id) => (int) $id)
			->all();
	}


	/**
	 * Generate and download a personalized info PDF for a quiz student (or guest).
	 */
	public function downloadStudentInfoPdf(Quiz $quiz, Request $request): Response|BinaryFileResponse
	{
		$this->authorizeQuizAccess($quiz);

		App::setLocale($quiz->language ?? config('app.locale'));

		$student = QuizStudent::query()
			->where('quiz_id', $quiz->id)
			->where('student_code', $request->student_code)
			->first();

		if (!$student) {
			abort(404, __('controllers.student_not_found'));
		}

		$quiz->load('creator');

		$isGuest = $student->student_code === '0000';
		$guestUrl = $isGuest ? $quiz->publicAccessUrl() : null;
		$showPinAccess = !$isGuest && $quiz->supportsStudentPinAccess();
		$showPersonalLink = !$isGuest && $quiz->supportsStudentPersonalLinks();
		$studentUrl = $showPersonalLink ? $student->accessLinkUrl() : null;
		$pinJoinUrl = $showPinAccess ? route('quiz.join') : null;
		$qrTargetUrl = $guestUrl ?? $studentUrl ?? $pinJoinUrl;
		$qrSvg = $qrTargetUrl
			? base64_encode(QrCode::format('svg')->size(150)->generate($qrTargetUrl))
			: null;

		$data = [
			'quiz' => $quiz,
			'student' => $student,
			'join_url' => route('quiz.join'),
			'student_url' => $studentUrl,
			'guest_url' => $guestUrl,
			'pin_join_url' => $pinJoinUrl,
			'qr_svg' => $qrSvg,
			'is_guest' => $isGuest,
			'show_pin_access' => $showPinAccess,
			'show_personal_link' => $showPersonalLink,
		];

		$pdf = Pdf::loadView('quiz_attempts.student_info_pdf', $data);

		return $pdf->download('quiz_info_' . $student->student_code . '.pdf');
	}

	/**
	 * Generate and download a certificate of completion for a passed quiz attempt.
	 */
	public function downloadCertificate(QuizAttempt $attempt): Response|BinaryFileResponse
	{
		$quiz = $this->resolveQuizFromAttempt($attempt);
		$this->authorizeQuizAccess($quiz);

		App::setLocale($quiz->language ?? config('app.locale'));

		// User is not eligible for certificate
		if ($attempt->score < $quiz->pass_percentage) {
			abort(403, __('controllers.certificate_not_eligible'));
		}

		// Ensure submission date is properly formatted
		$attempt->submitted_at = Carbon::parse($attempt->submitted_at);

		$pdf = Pdf::loadView('quiz_attempts.certificate', [
			'attempt' => $attempt,
			'quiz' => $quiz,
		])->setPaper('A4', 'landscape');

		return $pdf->download('certificate_' . $attempt->id . '.pdf');
	}

	/**
	 * Verify a quiz attempt by ID and display relevant information.
	 */
	public function verifyAttempt(Request $request, int $id): View
	{
		$isSignedRequest = $request->hasValidSignature();

		if (!$isSignedRequest && !auth()->check()) {
			abort(404);
		}

		$attempt = QuizAttempt::with('quiz')->find($id);

		if (!$attempt) {
			return view('quiz_attempts.verify_missing', [
				'attempt_id' => $id,
			]);
		}

		$quiz = $this->resolveQuizFromAttempt($attempt);

        if ($isSignedRequest && !$quiz->usesCertificateVerification()) {
            abort(404);
        }

		if (!$isSignedRequest) {
			$this->authorizeQuizAccess($quiz);
		}

		return view('quiz_attempts.verify', [
			'attempt' => $attempt,
			'quiz' => $quiz,
			'isPublicVerification' => $isSignedRequest,
		]);
	}

	/**
	 * Ensure that the attempt belongs to the given quiz.
	 *
	 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
	private function ensureAttemptBelongsToQuiz(Quiz $quiz, QuizAttempt $attempt): void
	{
		if ($attempt->quiz_id !== $quiz->id) {
			abort(404, __('controllers.attempt_quiz_mismatch'));
		}
	}

	/**
	 * Export all attempts for the given quiz as an Excel (.xlsx) file.
	 */
	public function exportToExcel(Quiz $quiz): BinaryFileResponse
	{
		$this->authorizeQuizAccess($quiz);

		$filename = 'quiz_attempts_' . $quiz->id . '_' . now()->format('Ymd_His') . '.xlsx';

		return Excel::download(new QuizAttemptsExport($quiz->id), $filename);
	}

	/**
	 * Viewing an individual quiz attempt is disabled. Redirect to the attempt list.
	 */
	public function show(Quiz $quiz, QuizAttempt $attempt): RedirectResponse
	{
		return redirect()
			->route('quizzes.quiz_attempts.index', $quiz)
			->with('error', __('controllers.attempt_view_disabled'));
	}

	/**
	 * Display per-question answer statistics for the quiz.
	 */
    public function questionStats(Quiz $quiz): View
    {
        $this->authorizeQuizAccess($quiz);

        $stats = $this->buildQuestionStats($quiz);

		return view('quiz_attempts.question_stats', compact('quiz', 'stats'));
	}

	/**
	 * Export per-question answer statistics as Excel.
	 */
    public function exportQuestionStats(Quiz $quiz): BinaryFileResponse
    {
        $this->authorizeQuizAccess($quiz);

		$stats = collect($this->buildQuestionStats($quiz))
			->values()
			->map(function (array $row, int $index): array {
				return [
					'#' => $index + 1,
					__('quizzes.question_stat') => Str::limit(strip_tags($row['question']), 100),
					__('quizzes.correct_stats') => $row['correct'],
					__('quizzes.incorrect_stats') => $row['wrong'],
					__('quizzes.unanswered_stats') => $row['unanswered'],
					__('quizzes.score_stats') => $row['success_rate'] . '%',
				];
			});

		$filename = 'quiz_stats_' . now()->format('Ymd_His') . '.xlsx';

		return Excel::download(
			new class($stats) implements FromCollection, WithHeadings {
				protected Collection $stats;

				public function __construct(Collection $stats)
				{
					$this->stats = $stats;
				}

				public function collection(): Collection
				{
					return $this->stats;
				}

				public function headings(): array
				{
					return ['#', __('quizzes.question_text'), __('quizzes.correct_stats'), __('quizzes.incorrect_stats'), __('quizzes.unanswered_stats'), __('quizzes.score_stats')];
				}
			},
			$filename
		);
	}

	/**
	 * Build consistent per-question statistics for the page and export.
	 *
	 * @return array<int, array{question:string,correct:int,wrong:int,unanswered:int,eligible:int,answered:int,success_rate:float}>
	 */
	private function buildQuestionStats(Quiz $quiz): array
	{
		$questions = $quiz->questions()->with('answers')->get();
		$attempts = QuizAttempt::query()
			->where('quiz_id', $quiz->id)
			->whereNotNull('submitted_at')
			->with('answers')
			->get();

		$stats = [];

		foreach ($questions as $question) {
			$correctAnswerIds = $question->answers
				->where('is_correct', true)
				->pluck('id')
				->sort()
				->values()
				->all();

			$eligibleCount = 0;
			$answeredCount = 0;
			$correctCount = 0;

			foreach ($attempts as $attempt) {
				if (!$this->attemptIncludesQuestion($quiz, $attempt, $question->id)) {
					continue;
				}

				$eligibleCount++;

				$selectedIds = $attempt->answers
					->where('question_id', $question->id)
					->pluck('answer_id')
					->sort()
					->values()
					->all();

				if (empty($selectedIds)) {
					continue;
				}

				$answeredCount++;

				if (!empty($correctAnswerIds) && $selectedIds === $correctAnswerIds) {
					$correctCount++;
				}
			}

			$wrongCount = max($answeredCount - $correctCount, 0);
			$unansweredCount = max($eligibleCount - $answeredCount, 0);
			$successRate = $answeredCount > 0 ? round(($correctCount / $answeredCount) * 100, 1) : 0.0;

			$stats[] = [
				'question' => $question->text,
				'correct' => $correctCount,
				'wrong' => $wrongCount,
				'unanswered' => $unansweredCount,
				'eligible' => $eligibleCount,
				'answered' => $answeredCount,
				'success_rate' => $successRate,
			];
		}

		return $stats;
	}

	/**
	 * Determine whether a submitted attempt actually included the specific question.
	 */
	private function attemptIncludesQuestion(Quiz $quiz, QuizAttempt $attempt, int $questionId): bool
	{
		$questionOrder = collect($attempt->question_order ?? [])
			->filter(fn ($id) => is_numeric($id))
			->map(fn ($id) => (int) $id)
			->values()
			->all();

		if (!empty($questionOrder)) {
			return in_array($questionId, $questionOrder, true);
		}

		return !$quiz->is_random_order || !$quiz->questions_limit;
	}

	/**
	 * Forcefully submit a quiz attempt if eligible and not already submitted.
	 */
	public function forceSubmit(Request $request): Response
	{
		$attemptId = $request->input('attempt_id');

		// Reject guest or empty attempt ID
		if (!$attemptId || $attemptId === 'guest') {
			return response()->noContent();
		}

		$attempt = QuizAttempt::with('quiz')->find($attemptId);

		// Only the session that owns the attempt may auto-submit it on unload.
		if (
			!$attempt ||
			!$attempt->quiz ||
			!$this->sessionOwnsAttempt($attempt) ||
			$attempt->quiz->allow_resume ||
			!$attempt->isInProgress()
		) {
			return response()->noContent();
		}

		$this->attemptLifecycle->submit($attempt, $attempt->quiz);

		return response()->noContent();
	}
}
