<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesQuizParticipantAccess;
use App\Http\Controllers\Concerns\HandlesQuizParticipantPublicPool;
use App\Http\Controllers\Concerns\HandlesQuizParticipantSession;
use App\Mail\QuizSuccessNotificationMail;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\QuizStudent;
use App\Services\AttemptLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuizParticipantController extends Controller
{
    use HandlesQuizParticipantAccess;
    use HandlesQuizParticipantPublicPool;
    use HandlesQuizParticipantSession;

    public function __construct(
        private readonly AttemptLifecycleService $attemptLifecycle
    ) {}

    /**
     * Show the form where the student can enter a quiz code.
     */
    public function showJoinForm(): View
    {
        App::setLocale(Session::get('locale', config('app.locale')));

        return view('quiz.join');
    }

    /**
     * Show a friendly message when a different quiz has replaced the active participant session.
     */
    public function showSessionConflict(): View
    {
        $quiz = Quiz::find(Session::get('quiz_id'));

        if ($quiz) {
            App::setLocale($quiz->resolvedLocale(config('app.locale')));
        }

        return view('quiz.session_conflict', [
            'quiz' => $quiz,
            'studentName' => Session::get('student_name'),
            'canContinue' => Session::has('attempt_id') && $quiz !== null,
        ]);
    }

    /**
     * Show the form where a student enters their access code after joining a quiz.
     */
    public function showStudentForm(): View|RedirectResponse
    {
        if (! Session::has('quiz_id')) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.missing_code'));
        }

        $quiz = Quiz::find(Session::get('quiz_id'));

        if (! $quiz) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.quiz_not_found'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensureStudentCodeScreenIsAllowed($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $quizRouteKey = $this->ensureQuizRouteToken($quiz);
        $view = $this->resolveParticipantView($quiz, 'student');

        return view($view, compact('quiz', 'quizRouteKey'));
    }

    /**
     * Validate the quiz code entered by the student and store quiz session.
     */
    public function validateQuizCode(Request $request): RedirectResponse
    {
        $request->validate([
            'quiz_code' => 'required|string|size:8|exists:quizzes,quiz_code',
        ]);

        $quiz = Quiz::where('quiz_code', $request->quiz_code)->first();

        if (! $quiz || $quiz->status !== 'active') {
            return redirect()->route('quiz.join')
                ->with('error', __('join.quiz_code_error'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureStudentCodeScreenIsAllowed($quiz)) {
            return $redirect;
        }

        $this->resetQuizRuntimeState();
        Session::put('quiz_id', $quiz->id);
        Session::put('quiz_code', $quiz->quiz_code);
        $this->ensureQuizRouteToken($quiz);

        return redirect()->route('quiz.join_student');
    }

    /**
     * Validate student code and initialize quiz attempt session.
     */
    public function validateStudentCode(Request $request): RedirectResponse
    {
        $request->validate([
            'student_code' => 'required|string|size:4',
        ]);

        $quiz = Quiz::find(Session::get('quiz_id'));

        if (! $quiz) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.quiz_not_found'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        $this->ensureQuizRouteToken($quiz);

        if ($request->student_code === '0000') {
            if (! $quiz->allow_guest) {
                return redirect()->route('quiz.join')
                    ->with('error', __('join.guests_not_allowed'));
            }

            if ($quiz->usesLearningMode()) {
                $this->syncSessionForLearningMode($quiz, '0000', __('join.guest_name'));

                return redirect()->route('quiz.start')
                    ->with('success', __('join.learning_mode_start'));
            }

            $this->resetQuizRuntimeState();
            Session::put('attempt_id', 'guest');
            Session::put('student_code', '0000');
            Session::put('student_name', __('join.guest_name'));
            Session::put('quiz_id', $quiz->id);
            Session::put('guest_started_at', now());
            $this->ensureQuizRouteToken($quiz);

            return redirect()->route('quiz.start')
                ->with('success', __('join.guest_start'));
        }

        if ($redirect = $this->ensureStudentPinAccessIsAllowed($quiz)) {
            return $redirect;
        }

        $student = QuizStudent::where('quiz_id', $quiz->id)
            ->where('student_code', $request->student_code)
            ->first();

        if (! $student) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.student_not_found'));
        }

        if ($quiz->usesLearningMode()) {
            $this->syncSessionForLearningMode($quiz, $student->student_code, $student->student_name);

            return redirect()->route('quiz.start')
                ->with('success', __('join.learning_mode_start'));
        }

        $attempts = $this->attemptsForStudent($student)
            ->orderByDesc('created_at')
            ->get();

        $maxAllowed = $student->max_attempts ?? 1;
        $completed = $attempts->whereNotNull('submitted_at')->count();

        if ($completed >= $maxAllowed) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.max_attempts_exceeded'));
        }

        $ongoing = $attempts->firstWhere('submitted_at', null);

        if ($ongoing) {
            $ongoing = $this->attemptLifecycle->expireIfNeeded($ongoing, $quiz);

            if (! $ongoing->isInProgress()) {
                $ongoing = null;
            }
        }

        if ($ongoing && ! $quiz->allow_resume) {
            $ongoing->answers()->delete();
            $this->attemptLifecycle->abandon($ongoing, true);
            $ongoing = null;
        }

        if ($ongoing) {
            $this->resetQuizRuntimeState();
            Session::put('attempt_id', $ongoing->id);
            Session::put('student_code', $ongoing->student_code);
            Session::put('student_name', $ongoing->student_name);
            Session::put('quiz_id', $quiz->id);
            $this->ensureQuizRouteToken($quiz);
            $this->syncSessionFromAttempt($ongoing);

            return redirect()->route('quiz.start')
                ->with('success', __('join.resume_attempt'));
        }

        $newAttempt = $quiz->attempts()->create([
            'quiz_student_id' => $student->id,
            'student_code' => $request->student_code,
            'student_name' => $student->student_name,
            'max_attempts' => $maxAllowed,
            'score' => 0,
            'status' => QuizAttempt::STATUS_IN_PROGRESS,
        ]);

        $this->resetQuizRuntimeState();
        Session::put('attempt_id', $newAttempt->id);
        Session::put('student_code', $newAttempt->student_code);
        Session::put('student_name', $newAttempt->student_name);
        Session::put('quiz_id', $quiz->id);
        $this->ensureQuizRouteToken($quiz);

        return redirect()->route('quiz.start')
            ->with('success', __('join.new_attempt'));
    }

    /**
     * Show the quiz introduction screen before questions begin.
     */
    public function start(): View|RedirectResponse
    {
        if (! Session::has('attempt_id') || ! Session::has('student_name')) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.no_session'));
        }

        $quiz = Quiz::find(Session::get('quiz_id'));

        if (! $quiz) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.quiz_not_found'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        $quizRouteKey = $this->ensureQuizRouteToken($quiz);
        $viewName = $this->resolveParticipantView($quiz, 'start');

        return view($viewName, compact('quiz', 'quizRouteKey'));
    }

    /**
     * Start the quiz and redirect to the first question.
     */
    public function startQuestion(string $quizKey): RedirectResponse
    {
        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }

        if (! Session::has('attempt_id')) {
            return redirect()->route('quiz.join')->with('error', __('join.no_session'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $attemptId = Session::get('attempt_id');
        $attempt = $attemptId === 'guest' ? null : QuizAttempt::find($attemptId);

        if ($attemptId !== 'guest') {
            if (! $attempt || $attempt->quiz_id !== $quiz->id) {
                return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
            }

            $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);

            if (! $attempt->isInProgress()) {
                $this->syncSessionFromAttempt($attempt);

                return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)])
                    ->with('error', __('join.previous_attempt_expired'));
            }
        }

        if (! $quiz->allow_resume && Session::has('quiz_end_time')) {
            Session::forget([
                'attempt_id',
                'question_order',
                'quiz_end_time',
                'answer_order_map',
                'guest_answers',
                'guest_answered',
            ]);

            return redirect()->route('quiz.join')
                ->with('error', __('join.cannot_resume'));
        }

        if ($quiz->has_timer) {
            $now = now()->timestamp;

            if (Session::has('quiz_end_time') && $now >= Session::get('quiz_end_time')) {
                Session::forget([
                    'attempt_id',
                    'question_order',
                    'quiz_end_time',
                    'answer_order_map',
                    'guest_answers',
                    'guest_answered',
                ]);

                return redirect()->route('quiz.join')
                    ->with('error', __('join.previous_attempt_expired'));
            }

            if (! Session::has('quiz_end_time')) {
                $endTime = $attemptId === 'guest'
                    ? now()->addSeconds($quiz->time_limit)->timestamp
                    : optional($attempt?->expires_at)?->timestamp;

                if ($endTime) {
                    Session::put('quiz_end_time', $endTime);
                }
            }
        }

        if (! Session::has('question_order')) {
            $questions = $attempt && is_array($attempt->question_order) && ! empty($attempt->question_order)
                ? $attempt->question_order
                : $quiz->questions()->pluck('id')->toArray();

            if ($quiz->is_random_order && (! $attempt || empty($attempt->question_order))) {
                shuffle($questions);

                if ($quiz->questions_limit && $quiz->questions_limit < count($questions)) {
                    $questions = array_slice($questions, 0, $quiz->questions_limit);
                }
            }

            Session::put('question_order', $questions);
            Session::forget('answer_order_map');
        }

        if ($attempt) {
            $attempt = $this->attemptLifecycle->beginAttempt($attempt, $quiz, Session::get('question_order', []));
            $this->syncSessionFromAttempt($attempt);
        } elseif (! Session::has('current_question_index')) {
            $this->setCurrentQuestionIndex(0);
        }

        if (! Session::has('current_question_index')) {
            $this->setCurrentQuestionIndex(0);
        }

        $this->ensureQuestionRouteMap(Session::get('question_order', []));

        $firstQuestionId = $this->getExpectedQuestionId(Session::get('question_order', []));

        if (! $firstQuestionId) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.no_questions'));
        }

        return redirect()->route('quiz.question', [
            'quizKey' => $this->ensureQuizRouteToken($quiz),
            'questionKey' => $this->getQuestionRouteToken($firstQuestionId),
        ]);
    }

    /**
     * Skip the current question and redirect to the next unanswered one.
     */
    public function skipQuestion(string $quizKey, string $questionKey): RedirectResponse
    {
        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }
        $question = $this->resolveQuestionFromRoute($questionKey, $quiz);

        if (! Session::has('attempt_id') || ! Session::has('question_order')) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.no_session'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $attemptId = Session::get('attempt_id');
        $questionOrder = Session::get('question_order');

        if ($attemptId !== 'guest') {
            $attempt = QuizAttempt::find($attemptId);
            if (! $attempt) {
                return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
            }

            $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);
            $this->syncSessionFromAttempt($attempt);

            if (! $attempt->isInProgress()) {
                return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
            }
        }

        if ($redirect = $this->enforceCurrentQuestion($quiz, $question, $questionOrder)) {
            return $redirect;
        }

        if ($attemptId === 'guest') {
            $this->advanceAttemptQuestionIndex(null, $question->id, true);
        } else {
            $this->advanceAttemptQuestionIndex($attempt, $question->id, true);
        }

        return $this->redirectToCurrentQuestionOrEnd($quiz, Session::get('question_order', []));
    }

    /**
     * Submit selected answer(s) for the current question.
     */
    public function submitAnswer(Request $request, string $quizKey, string $questionKey): RedirectResponse
    {
        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }
        $question = $this->resolveQuestionFromRoute($questionKey, $quiz);

        if (! Session::has('attempt_id') || ! Session::has('question_order')) {
            return redirect()->route('quiz.join')->with('error', __('join.no_session'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $attemptId = Session::get('attempt_id');
        $questionOrder = Session::get('question_order');

        if ($attemptId === 'guest' && $quiz->has_timer && Session::has('quiz_end_time')) {
            if (now()->timestamp >= Session::get('quiz_end_time')) {
                return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
            }
        }

        $request->validate([
            'answer_id' => 'required|array|min:1',
            'answer_id.*' => [
                'distinct',
                Rule::exists('answers', 'id')->where(
                    fn ($query) => $query->where('question_id', $question->id)
                ),
            ],
        ]);

        $selectedAnswers = $request->input('answer_id');
        $expectedCount = $question->correct_answers_count;

        if (count($selectedAnswers) !== $expectedCount) {
            return redirect()->back()
                ->with('error', trans_choice('join.must_select_exactly_n', $expectedCount, ['count' => $expectedCount]));
        }

        if ($redirect = $this->enforceCurrentQuestion($quiz, $question, $questionOrder)) {
            return $redirect;
        }

        if ($this->sessionUsesLearningMode($quiz)) {
            $this->storeLearningModeFeedback($question, $selectedAnswers);

            return redirect()->route('quiz.question', [
                'quizKey' => $this->ensureQuizRouteToken($quiz),
                'questionKey' => $this->getQuestionRouteToken($question->id),
            ]);
        }

        if ($attemptId === 'guest') {
            $guestAnswers = Session::get('guest_answers', []);
            $guestAnswers[$question->id] = $selectedAnswers;
            Session::put('guest_answers', $guestAnswers);

            $answered = array_values(array_unique(array_merge(
                Session::get('guest_answered', []),
                [$question->id]
            )));
            Session::put('guest_answered', $answered);
            $this->advanceAttemptQuestionIndex(null, $question->id);

            return $this->redirectToCurrentQuestionOrEnd($quiz, Session::get('question_order', []));
        }

        $attempt = QuizAttempt::find($attemptId);
        if (! $attempt) {
            return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
        }

        $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);
        $this->syncSessionFromAttempt($attempt);
        $questionOrder = Session::get('question_order', []);

        if (! $attempt->isInProgress()) {
            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        if ($redirect = $this->enforceCurrentQuestion($quiz, $question, $questionOrder)) {
            return $redirect;
        }

        QuizAttemptAnswer::where('attempt_id', $attempt->id)
            ->where('question_id', $question->id)
            ->delete();

        foreach ($selectedAnswers as $answerId) {
            $isCorrect = $question->answers()
                ->where('id', $answerId)
                ->where('is_correct', true)
                ->exists();

            QuizAttemptAnswer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
                'answer_id' => $answerId,
                'is_correct' => $isCorrect,
            ]);
        }

        $this->advanceAttemptQuestionIndex($attempt, $question->id);

        return $this->redirectToCurrentQuestionOrEnd($quiz, Session::get('question_order', []));
    }

    /**
     * Show the current question to the user based on session order.
     */
    public function showQuestion(string $quizKey, string $questionKey): View|RedirectResponse
    {
        if (! Session::has('attempt_id') || ! Session::has('question_order')) {
            return redirect()->route('quiz.join')->with('error', __('join.no_session'));
        }

        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }
        $question = $this->resolveQuestionFromRoute($questionKey, $quiz);

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $attemptId = Session::get('attempt_id');
        $questionOrder = Session::get('question_order', []);
        $currentIndex = $this->getCurrentQuestionIndex();

        if ($attemptId === 'guest') {
            $quizId = Session::get('quiz_id');
            if ($quiz->id !== $quizId) {
                abort(404);
            }

            if ($redirect = $this->enforceCurrentQuestion($quiz, $question, $questionOrder)) {
                return $redirect;
            }

            $totalQuestions = count($questionOrder);
            $isReviewPass = $this->isReviewPass($questionOrder);
            $isLastQuestion = $this->isLastQuestionInFlow($questionOrder);
            $displayQuestionIndex = array_search($question->id, $questionOrder, true);
            $displayQuestionIndex = $displayQuestionIndex === false ? $currentIndex : (int) $displayQuestionIndex;
            $questionProgressLabel = $this->buildQuestionProgressLabel(
                $isReviewPass,
                $displayQuestionIndex,
                $totalQuestions
            );

            $timeRemaining = 0;
            if (Session::has('quiz_end_time')) {
                $endTimestamp = Session::get('quiz_end_time');
                $timeRemaining = max(0, $endTimestamp - now()->timestamp);
                if ($timeRemaining <= 0) {
                    return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
                }
            }

            $viewName = $this->resolveParticipantView($quiz, 'question');
            $orderedAnswers = $this->getOrderedAnswersForQuestion($quiz, $question);
            $question->setRelation('answers', $orderedAnswers);
            $learningFeedback = $this->sessionUsesLearningMode($quiz)
                ? $this->currentLearningModeFeedback($question)
                : null;

            return view($viewName, [
                'quiz' => $quiz,
                'quizRouteKey' => $this->ensureQuizRouteToken($quiz),
                'question' => $question,
                'questionRouteKey' => $this->getQuestionRouteToken($question->id),
                'currentQuestionIndex' => $displayQuestionIndex,
                'questionProgressLabel' => $questionProgressLabel,
                'totalQuestions' => $totalQuestions,
                'isLastQuestion' => $isLastQuestion,
                'isReviewPass' => $isReviewPass,
                'timeRemaining' => $timeRemaining,
                'allowDisplay' => true,
                'correctCount' => $orderedAnswers->where('is_correct', true)->count(),
                'isLearningMode' => $this->sessionUsesLearningMode($quiz),
                'showLearningFeedback' => $learningFeedback !== null,
                'learningSelectedAnswerIds' => $learningFeedback['selectedAnswerIds'] ?? [],
                'learningCorrectAnswerIds' => $learningFeedback['correctAnswerIds'] ?? [],
                'learningAnswerWasCorrect' => $learningFeedback['isCorrect'] ?? false,
            ]);
        }

        $attempt = QuizAttempt::find($attemptId);
        if (! $attempt) {
            return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
        }

        $attemptQuiz = $attempt->quiz;
        $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $attemptQuiz);
        $this->syncSessionFromAttempt($attempt);
        $questionOrder = Session::get('question_order', []);
        $currentIndex = $this->getCurrentQuestionIndex();

        if (! $attempt->isInProgress()) {
            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($attemptQuiz)]);
        }

        if ($quiz->id !== $attemptQuiz->id) {
            abort(404);
        }

        if ($redirect = $this->enforceCurrentQuestion($attemptQuiz, $question, $questionOrder)) {
            return $redirect;
        }

        $totalQuestions = count($questionOrder);
        $isReviewPass = $this->isReviewPass($questionOrder);
        $isLastQuestion = $this->isLastQuestionInFlow($questionOrder);
        $displayQuestionIndex = array_search($question->id, $questionOrder, true);
        $displayQuestionIndex = $displayQuestionIndex === false ? $currentIndex : (int) $displayQuestionIndex;
        $questionProgressLabel = $this->buildQuestionProgressLabel(
            $isReviewPass,
            $displayQuestionIndex,
            $totalQuestions
        );

        $timeRemaining = 0;
        if ($attempt->expires_at) {
            $endTimestamp = $attempt->expires_at->timestamp;
            $timeRemaining = max(0, $endTimestamp - now()->timestamp);

            if ($timeRemaining <= 0) {
                return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($attemptQuiz)]);
            }
        }

        $viewName = $this->resolveParticipantView($attemptQuiz, 'question');
        $orderedAnswers = $this->getOrderedAnswersForQuestion($attemptQuiz, $question);
        $question->setRelation('answers', $orderedAnswers);
        $learningFeedback = $this->sessionUsesLearningMode($attemptQuiz)
            ? $this->currentLearningModeFeedback($question)
            : null;

        return view($viewName, [
            'quiz' => $attemptQuiz,
            'quizRouteKey' => $this->ensureQuizRouteToken($attemptQuiz),
            'question' => $question,
            'questionRouteKey' => $this->getQuestionRouteToken($question->id),
            'currentQuestionIndex' => $displayQuestionIndex,
            'questionProgressLabel' => $questionProgressLabel,
            'totalQuestions' => $totalQuestions,
            'isLastQuestion' => $isLastQuestion,
            'isReviewPass' => $isReviewPass,
            'timeRemaining' => $timeRemaining,
            'allowDisplay' => true,
            'correctCount' => $orderedAnswers->where('is_correct', true)->count(),
            'isLearningMode' => $this->sessionUsesLearningMode($attemptQuiz),
            'showLearningFeedback' => $learningFeedback !== null,
            'learningSelectedAnswerIds' => $learningFeedback['selectedAnswerIds'] ?? [],
            'learningCorrectAnswerIds' => $learningFeedback['correctAnswerIds'] ?? [],
            'learningAnswerWasCorrect' => $learningFeedback['isCorrect'] ?? false,
        ]);
    }

    /**
     * Skip to the next unanswered question or finish if all answered.
     */
    public function nextQuestion(string $quizKey, string $currentQuestionKey): RedirectResponse
    {
        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }
        $currentQuestion = $this->resolveQuestionFromRoute($currentQuestionKey, $quiz);

        if (! Session::has('attempt_id') || ! Session::has('question_order')) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.no_session'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $questionOrder = Session::get('question_order', []);
        if ($redirect = $this->enforceCurrentQuestion($quiz, $currentQuestion, $questionOrder)) {
            return $redirect;
        }

        $attemptId = Session::get('attempt_id');
        if ($attemptId !== 'guest') {
            $attempt = QuizAttempt::find($attemptId);
            if (! $attempt) {
                return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
            }

            $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);
            $this->syncSessionFromAttempt($attempt);

            if (! $attempt->isInProgress()) {
                return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
            }
        } elseif (
            $quiz->has_timer &&
            Session::has('quiz_end_time') &&
            now()->timestamp >= Session::get('quiz_end_time')
        ) {
            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        if ($this->sessionUsesLearningMode($quiz)) {
            $this->clearLearningModeFeedback();
            $this->advanceAttemptQuestionIndex(null, $currentQuestion->id);

            return $this->redirectToCurrentQuestionOrEnd($quiz, Session::get('question_order', []));
        }

        return $this->redirectToCurrentQuestionOrEnd($quiz, Session::get('question_order', []));
    }

    /**
     * Final submission of the quiz by the student.
     */
    public function submitFinal(Request $request, string $quizKey): RedirectResponse
    {
        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }

        if (! Session::has('attempt_id') || ! Session::has('question_order')) {
            return redirect()->route('quiz.join')->with('error', __('join.no_session'));
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        if ($redirect = $this->ensureQuizIsActiveForParticipantFlow($quiz)) {
            return $redirect;
        }

        if ($redirect = $this->ensurePublicAnonymousPoolReservationIsActive($quiz)) {
            return $redirect;
        }

        $attemptId = Session::get('attempt_id');
        $questionOrder = Session::get('question_order', []);

        if ($this->sessionUsesLearningMode($quiz)) {
            if ($request->filled('current_question_key') && $request->has('answer_id')) {
                $question = $this->resolveQuestionFromRoute((string) $request->string('current_question_key'), $quiz);
                if ($redirect = $this->enforceCurrentQuestion($quiz, $question, $questionOrder)) {
                    return $redirect;
                }

                $request->validate([
                    'answer_id' => 'required|array|min:1',
                    'answer_id.*' => [
                        'distinct',
                        Rule::exists('answers', 'id')->where(
                            fn ($query) => $query->where('question_id', $question->id)
                        ),
                    ],
                ]);

                $selectedAnswers = $request->input('answer_id');
                $expectedCount = $question->correct_answers_count;

                if (count($selectedAnswers) !== $expectedCount) {
                    return redirect()->back()
                        ->with('error', trans_choice('join.must_select_exactly_n', $expectedCount, ['count' => $expectedCount]));
                }

                $this->storeLearningModeFeedback($question, $selectedAnswers);

                return redirect()->route('quiz.question', [
                    'quizKey' => $this->ensureQuizRouteToken($quiz),
                    'questionKey' => $this->getQuestionRouteToken($question->id),
                ]);
            }

            $this->clearLearningModeFeedback();

            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        if ($request->filled('current_question_key') && $request->has('answer_id')) {
            $question = $this->resolveQuestionFromRoute((string) $request->string('current_question_key'), $quiz);
            if ($redirect = $this->enforceCurrentQuestion($quiz, $question, $questionOrder)) {
                return $redirect;
            }

            $request->validate([
                'answer_id' => 'required|array|min:1',
                'answer_id.*' => [
                    'distinct',
                    Rule::exists('answers', 'id')->where(
                        fn ($query) => $query->where('question_id', $question->id)
                    ),
                ],
            ]);

            $selectedAnswers = $request->input('answer_id');
            $expectedCount = $question->correct_answers_count;

            if (count($selectedAnswers) !== $expectedCount) {
                return redirect()->back()
                    ->with('error', trans_choice('join.must_select_exactly_n', $expectedCount, ['count' => $expectedCount]));
            }

            if ($attemptId === 'guest') {
                $guestAnswers = Session::get('guest_answers', []);
                $guestAnswers[$question->id] = $selectedAnswers;
                Session::put('guest_answers', $guestAnswers);
                Session::put('guest_answered', array_values(array_unique(array_merge(Session::get('guest_answered', []), [$question->id]))));
                $this->advanceAttemptQuestionIndex(null, $question->id);
            } else {
                $attempt = QuizAttempt::find($attemptId);
                if (! $attempt) {
                    return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
                }

                $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);
                $this->syncSessionFromAttempt($attempt);

                if (! $attempt->isInProgress()) {
                    return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
                }

                QuizAttemptAnswer::where('attempt_id', $attempt->id)
                    ->where('question_id', $question->id)
                    ->delete();

                foreach ($selectedAnswers as $answerId) {
                    $isCorrect = $question->answers()
                        ->where('id', $answerId)
                        ->where('is_correct', true)
                        ->exists();

                    QuizAttemptAnswer::create([
                        'attempt_id' => $attempt->id,
                        'question_id' => $question->id,
                        'answer_id' => $answerId,
                        'is_correct' => $isCorrect,
                    ]);
                }

                $this->advanceAttemptQuestionIndex($attempt, $question->id);
            }
        }

        if ($attemptId === 'guest') {
            if ($this->sessionUsesPublicAnonymousPool($quiz)) {
                $persistedAttempt = $this->persistPublicAnonymousPoolSubmission($quiz);

                if (! $persistedAttempt) {
                    $this->resetQuizRuntimeState();

                    return redirect()->route('quiz.join')
                        ->with('error', __('join.public_pool_slot_expired'));
                }

                Session::put('attempt_id', $persistedAttempt->id);
                Session::put('student_code', $persistedAttempt->student_code);
                Session::put('student_name', $persistedAttempt->student_name);
                Session::forget([
                    'public_anonymous_pool_reservation_id',
                    'public_anonymous_pool_slot_code',
                    'public_anonymous_pool_active',
                ]);
                $this->syncSessionFromAttempt($persistedAttempt);

                return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
            }

            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        $attempt = QuizAttempt::find($attemptId);
        if (! $attempt) {
            return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
        }

        $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);
        $this->syncSessionFromAttempt($attempt);

        if (! $attempt->isInProgress()) {
            return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
        }

        $attempt = $this->attemptLifecycle->submit($attempt, $quiz);
        $this->syncSessionFromAttempt($attempt);

        return redirect()->route('quiz.end', ['quizKey' => $this->ensureQuizRouteToken($quiz)]);
    }

    /**
     * Final screen after quiz submission. Shows results for guest or student.
     */
    public function endQuiz(string $quizKey): View|RedirectResponse
    {
        if (! Session::has('attempt_id')) {
            return redirect()->route('quiz.join')->with('error', __('join.no_session'));
        }

        $quiz = $this->resolveQuizFromRoute($quizKey);
        if ($quiz instanceof RedirectResponse) {
            return $quiz;
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));

        $attemptId = Session::get('attempt_id');
        $questionOrder = Session::get('question_order', []);
        $totalQuestions = count($questionOrder);

        $questions = $quiz->questions->keyBy('id');
        $correctCount = 0;
        $scorePercentage = 0;

        if ($this->sessionUsesLearningMode($quiz)) {
            $fakeAttempt = new QuizAttempt([
                'student_code' => (string) Session::get('student_code', '0000'),
                'student_name' => (string) Session::get('student_name', __('join.guest_name')),
                'score' => 0,
            ]);

            $this->resetQuizRuntimeState();

            return $this->renderResultView($quiz, $fakeAttempt, $totalQuestions, 0, 0, [
                'isLearningModeResult' => true,
            ]);
        }

        if ($attemptId === 'guest') {
            $fakeAttempt = new QuizAttempt([
                'student_code' => '0000',
                'student_name' => $this->sessionUsesPublicAnonymousPool($quiz)
                    ? __('controllers.anonymous_student_name')
                    : __('join.guest_name'),
                'score' => 0,
            ]);

            $guestAnswers = Session::get('guest_answers', []);

            foreach ($questionOrder as $questionId) {
                $question = $questions[$questionId] ?? null;
                $studentAnswerIds = collect($guestAnswers[$questionId] ?? [])->sort()->values()->all();
                $correctAnswerIds = $question?->answers()
                    ->where('is_correct', true)
                    ->pluck('id')
                    ->sort()
                    ->values()
                    ->all();

                if (
                    ! empty($question) &&
                    count($correctAnswerIds) === count($studentAnswerIds) &&
                    empty(array_diff($correctAnswerIds, $studentAnswerIds))
                ) {
                    $correctCount++;
                }
            }

            $scorePercentage = $totalQuestions > 0 ? ($correctCount / $totalQuestions) * 100 : 0;
            if ($this->sessionUsesPublicAnonymousPool($quiz)) {
                $this->releasePublicAnonymousPoolReservation($quiz);
            }

            Session::forget([
                'attempt_id', 'question_order', 'guest_answers', 'guest_answered', 'quiz_end_time', 'question_route_map', 'answer_order_map', 'current_question_index',
            ]);

            return $this->renderResultView($quiz, $fakeAttempt, $totalQuestions, $correctCount, $scorePercentage);
        }

        $attempt = QuizAttempt::find($attemptId);
        if (! $attempt) {
            return redirect()->route('quiz.join')->with('error', __('join.attempt_not_found'));
        }

        $attempt = $this->attemptLifecycle->expireIfNeeded($attempt, $quiz);
        $this->syncSessionFromAttempt($attempt);

        if ($attempt->isInProgress()) {
            $attempt = $this->attemptLifecycle->submit($attempt, $quiz);
            $this->syncSessionFromAttempt($attempt);
        }

        foreach ($questionOrder as $questionId) {
            $question = $questions[$questionId] ?? null;
            if (! $question) {
                continue;
            }

            $correctIds = $question->answers()->where('is_correct', true)->pluck('id')->sort()->values()->all();
            $studentIds = QuizAttemptAnswer::where('attempt_id', $attempt->id)
                ->where('question_id', $questionId)
                ->pluck('answer_id')
                ->sort()
                ->values()
                ->all();

            if (
                ! empty($studentIds) &&
                count($correctIds) === count($studentIds) &&
                empty(array_diff($correctIds, $studentIds))
            ) {
                $correctCount++;
            }
        }

        $scorePercentage = (float) $attempt->score;

        if (
            $scorePercentage >= $quiz->pass_percentage
            && $quiz->shouldNotifyCreatorOnPass()
            && $quiz->creator?->email
        ) {
            try {
                Mail::to($quiz->creator->email)->queue(new QuizSuccessNotificationMail(
                    (string) $attempt->student_name,
                    (string) $quiz->title,
                    (float) round($scorePercentage, 2)
                ));
            } catch (\Exception $e) {
                // Ignore mail delivery issues in the participant flow.
            }
        }

        Session::forget([
            'attempt_id', 'question_order', 'quiz_end_time', 'skipped_questions', 'question_route_map', 'answer_order_map', 'current_question_index',
        ]);

        return $this->renderResultView($quiz, $attempt, $totalQuestions, $correctCount, $scorePercentage);
    }

    /**
     * Entry point for a student with a personal access token.
     */
    public function studentLink(Request $request, QuizStudent $student): View|RedirectResponse
    {
        if (! $student->matchesAccessLinkFingerprint((string) $request->query('key'))) {
            abort(404);
        }

        $quiz = $student->quiz()->firstOrFail();

        App::setLocale($quiz->resolvedLocale(config('app.locale')));
        $quizRouteKey = $this->ensureQuizRouteToken($quiz);

        $isBot = $this->isLinkPreviewBot($request);

        if ($quiz->status !== 'active') {
            return redirect()->route('quiz.join')
                ->with('error', __('join.quiz_inactive'));
        }

        if ($redirect = $this->ensureStudentPersonalLinkAccessIsAllowed($quiz)) {
            return $redirect;
        }

        if ($isBot) {
            $student_name = $student->student_name;

            return view('quiz.templates.default.start', compact('quiz', 'student_name', 'quizRouteKey'));
        }

        if ($quiz->usesLearningMode()) {
            $this->syncSessionForLearningMode($quiz, $student->student_code, $student->student_name);

            return redirect()->route('quiz.start')
                ->with('success', __('join.learning_mode_start'));
        }

        $completed = $this->attemptsForStudent($student)
            ->whereNotNull('submitted_at')
            ->count();

        $maxAllowed = $student->max_attempts ?? 1;

        if ($completed >= $maxAllowed) {
            return redirect()->route('quiz.join')
                ->with('error', __('join.max_attempts_exceeded'));
        }

        $existing = $this->attemptsForStudent($student)
            ->whereNull('submitted_at')
            ->first();

        if ($existing) {
            $existing = $this->attemptLifecycle->expireIfNeeded($existing, $quiz);

            if (! $existing->isInProgress()) {
                $existing = null;
            }
        }

        if ($existing && ! $quiz->allow_resume) {
            $existing->answers()->delete();
            $this->attemptLifecycle->abandon($existing, true);
            $existing = null;
        }

        if ($existing) {
            $this->resetQuizRuntimeState();
            Session::put('quiz_id', $quiz->id);
            Session::put('attempt_id', $existing->id);
            Session::put('student_code', $student->student_code);
            Session::put('student_name', $student->student_name);
            $this->ensureQuizRouteToken($quiz);
            $this->syncSessionFromAttempt($existing);

            return redirect()->route('quiz.start')
                ->with('success', __('join.resume_attempt'));
        }

        $attempt = $quiz->attempts()->create([
            'quiz_student_id' => $student->id,
            'student_code' => $student->student_code,
            'student_name' => $student->student_name,
            'max_attempts' => $student->max_attempts,
            'score' => 0,
            'status' => QuizAttempt::STATUS_IN_PROGRESS,
        ]);

        $this->resetQuizRuntimeState();
        Session::put('quiz_id', $quiz->id);
        Session::put('attempt_id', $attempt->id);
        Session::put('student_code', $student->student_code);
        Session::put('student_name', $student->student_name);
        $this->ensureQuizRouteToken($quiz);

        return redirect()->route('quiz.start')
            ->with('success', __('join.personal_link_start'));
    }

    /**
     * Start a public quiz as a guest using a public token.
     */
    public function publicStart(Request $request, Quiz $quiz): View|RedirectResponse
    {
        if (! $quiz->matchesPublicLinkFingerprint((string) $request->query('key'))) {
            abort(404);
        }

        $isPublicAnonymousPoolMode = $this->quizUsesPublicAnonymousPool($quiz);

        if (! $quiz->is_public || (! $quiz->allow_guest && ! $isPublicAnonymousPoolMode) || $quiz->status !== 'active') {
            abort(404);
        }

        App::setLocale($quiz->resolvedLocale(config('app.locale')));
        $quizRouteKey = $this->ensureQuizRouteToken($quiz);

        $isBot = $this->isLinkPreviewBot($request);

        if ($isBot) {
            $student_name = $isPublicAnonymousPoolMode
                ? __('controllers.anonymous_student_name')
                : __('join.guest_name');

            return view('quiz.templates.default.start', compact('quiz', 'student_name', 'quizRouteKey'));
        }

        if ($isPublicAnonymousPoolMode) {
            $reservation = $this->claimPublicAnonymousPoolReservation($quiz, $request);

            if (! $reservation) {
                return redirect()->route('quiz.join')
                    ->with('error', __('join.public_pool_full'));
            }

            $resetRuntimeState = (int) Session::get('quiz_id', 0) !== (int) $quiz->id
                || (int) Session::get('public_anonymous_pool_reservation_id', 0) !== (int) $reservation->id;

            $this->syncSessionForPublicAnonymousPool($quiz, $reservation, $resetRuntimeState);

            return redirect()
                ->route('quiz.start')
                ->with('success', __('join.public_pool_start'));
        }

        if ($quiz->usesLearningMode()) {
            $this->syncSessionForLearningMode($quiz, '0000', __('join.guest_name'));

            return redirect()
                ->route('quiz.start')
                ->with('success', __('join.learning_mode_start'));
        }

        $this->resetQuizRuntimeState();
        Session::put('quiz_id', $quiz->id);
        Session::put('student_code', '0000');
        Session::put('student_name', __('join.guest_name'));
        Session::put('attempt_id', 'guest');
        Session::put('guest_started_at', now());

        return redirect()
            ->route('quiz.start')
            ->with('success', __('join.guest_start'));
    }

    /**
     * Show the catalogue of public quizzes available to guests.
     */
    public function catalogue(Request $request): View
    {
        $categoryId = $request->get('category_id');

        $query = Quiz::with(['creator', 'category'])
            ->where('is_public', true)
            ->where(function ($nested) {
                $nested->where('allow_guest', true)
                    ->orWhere('is_public_anonymous_pool_mode', true);
            })
            ->where('status', 'active');

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $quizzes = $query->orderBy('title')->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('quizzes.catalogue', [
            'quizzes' => $quizzes,
            'categories' => $categories,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Social and messaging preview crawlers need HTML metadata, not participant redirects.
     */
    private function isLinkPreviewBot(Request $request): bool
    {
        $userAgent = strtolower($request->header('User-Agent', ''));

        return str_contains($userAgent, 'facebookexternalhit')
            || str_contains($userAgent, 'twitterbot')
            || str_contains($userAgent, 'linkedinbot')
            || str_contains($userAgent, 'slackbot')
            || str_contains($userAgent, 'discordbot')
            || str_contains($userAgent, 'viber');
    }
}
