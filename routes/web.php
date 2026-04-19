<?php

/**
 * web.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AnswerController,
    CategoryController,
    DashboardController,
    FeedbackController,
    LocaleController,
    ProfileController,
    QuotaRequestController,
    QuestionController,
    QuizAttemptController,
    QuizDisplaySessionController,
    QuizParticipantController,
    QuizController,
    SystemUpdateController,
    UpdateController,
    UserController,
    QuizTemplateController
};

// Public entry points that do not require an authenticated teacher session.
Route::get('/', [QuizParticipantController::class, 'showJoinForm'])->name('home');

// Signed links allow controlled access to generated documents without exposing them globally.
Route::get('quizzes/{quiz}/quiz_attempts/{quizAttempt}/pdf/signed', [QuizAttemptController::class, 'downloadPdf'])
    ->name('quiz_attempts.download_pdf_signed')
    ->middleware('signed');

Route::middleware('auth')->get('quizzes/{quiz}/quiz_attempts/{quizAttempt}/pdf', [QuizAttemptController::class, 'downloadPdf'])
    ->name('quiz_attempts.download_pdf');

Route::get('/verify/attempt/{attempt}', [QuizAttemptController::class, 'verifyAttempt'])
    ->name('quiz_attempts.verify');

Route::get('/lang/{locale}', [LocaleController::class, 'update'])->name('set.locale');

Route::view('/terms', 'terms')->name('terms');
Route::view('/privacy', 'privacy')->name('privacy');
Route::view('/about', 'about')->name('about');

// Public quiz links are still signed so they can be shared safely with specific audiences.
Route::get('/public/{quiz}', [QuizParticipantController::class, 'publicStart'])
    ->middleware('signed')
    ->name('quizzes.public.start');
Route::get('/student/{student}', [QuizParticipantController::class, 'studentLink'])
    ->middleware('signed')
    ->name('quizzes.student.link');

// Teacher and admin back-office routes require both authentication and verified email addresses.
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Feedback
    Route::get('/feedback', [FeedbackController::class, 'create'])->name('feedback.create');
    Route::post('/feedback', [FeedbackController::class, 'store'])
        ->middleware('throttle:' . config('security.throttle.feedback_attempts', '3,10'))
        ->name('feedback.store');
    Route::post('/quota-requests', [QuotaRequestController::class, 'store'])->name('quota_requests.store');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Import questions via CSV
    Route::post('quizzes/{quiz}/questions/import', [QuestionController::class, 'importCsv'])->name('quizzes.questions.import');

    // Resource controllers keep the conventional CRUD surface easy to discover for students.
    Route::resource('categories', CategoryController::class);
    Route::resource('quizzes', QuizController::class)->except(['show']);
    Route::resource('quizzes.questions', QuestionController::class);
    Route::resource('quizzes.questions.answers', AnswerController::class);
    Route::resource('quizzes.quiz_attempts', QuizAttemptController::class)
        ->parameters(['quiz_attempts' => 'attempt']);

    // Supplementary endpoints cover exports, reports, and answer-management screens outside CRUD defaults.
    Route::post('quizzes/{quiz}/duplicate', [QuizController::class, 'duplicate'])->name('quizzes.duplicate');
    Route::get('quizzes/{quiz}/printable-pdf', [QuizController::class, 'exportPrintablePdf'])->name('quizzes.printable_pdf');
    Route::get('quizzes/{quiz}/questions/{question}/manage-answers', [AnswerController::class, 'index'])->name('quizzes.questions.answers.manage');
    Route::get('quizzes/{quiz}/quiz_attempts/export/excel', [QuizAttemptController::class, 'exportToExcel'])->name('quiz_attempts.export_excel');
    Route::get('quizzes/{quiz}/student-info-pdf', [QuizAttemptController::class, 'downloadStudentInfoPdf'])->name('quiz_attempts.student_info_pdf');
    Route::get('quizzes/{quiz}/question-stats', [QuizAttemptController::class, 'questionStats'])->name('quiz_attempts.question_stats');
    Route::get('quizzes/{quiz}/question-stats-export', [QuizAttemptController::class, 'exportQuestionStats'])->name('quiz_attempts.question_stats_export');
    Route::get('quizzes/catalogue', [QuizParticipantController::class, 'catalogue'])->name('quizzes.catalogue');
    Route::get('quizzes/{quiz}/students/report', [QuizAttemptController::class, 'studentsReportPdf'])->name('quiz_attempts.students_report_pdf');
    Route::get('quizzes/{quiz}/anonymous-cards-pdf', [QuizAttemptController::class, 'downloadAnonymousCardsPdf'])->name('quiz_attempts.anonymous_cards_pdf');

    Route::post('quizzes/{quiz}/quiz_attempts/{quizAttempt}/submit', [QuizAttemptController::class, 'submit'])->name('quizzes.quiz_attempts.submit');
    Route::get('/quiz_attempts/{attempt}/certificate', [QuizAttemptController::class, 'downloadCertificate'])->name('quiz_attempts.certificate');
    Route::post('quizzes/{quiz}/students/{student}/display-session', [QuizDisplaySessionController::class, 'launch'])->name('quiz_display.launch');
    Route::post('quizzes/{quiz}/display-sessions/{quizDisplaySession}/terminate', [QuizDisplaySessionController::class, 'terminate'])->name('quiz_display.terminate');

    // Student registration routes stay nested under a quiz because codes are only unique inside that scope.
    Route::prefix('quizzes/{quiz}/register-students')->name('quiz_attempts.')->group(function () {
        Route::get('/', [QuizAttemptController::class, 'registerStudents'])->name('register_students');
        Route::post('/', [QuizAttemptController::class, 'storeStudent'])->name('store_student');
        Route::post('/anonymous', [QuizAttemptController::class, 'storeAnonymousStudents'])->name('store_anonymous_students');
        Route::post('/import', [QuizAttemptController::class, 'importStudents'])->name('import_students');
        Route::delete('/{student}', [QuizAttemptController::class, 'destroyStudent'])->name('destroy_student');
    });

    // Admin-only route groups are isolated so authorization intent is obvious at the routing layer.
    Route::middleware('can:manage-users')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Updates - only for admin
    Route::middleware('can:manage-updates')->group(function () {
        Route::resource('updates', UpdateController::class)->only(['create', 'store', 'destroy']);
        Route::get('system-updates', [SystemUpdateController::class, 'index'])->name('system_updates.index');
    });

    // Templates - only for admin
    Route::middleware('can:manage-templates')->group(function () {
        Route::resource('quiz_templates', QuizTemplateController::class);
    });

    // Updates - view for auth
    Route::get('updates', [UpdateController::class, 'index'])->name('updates.index');
});

Route::get('display/sessions/{quizDisplaySession}/screen', [QuizDisplaySessionController::class, 'screen'])
    ->middleware('signed')
    ->name('quiz_display.screen');
Route::get('display/sessions/{quizDisplaySession}/screen-state', [QuizDisplaySessionController::class, 'screenState'])
    ->middleware('signed')
    ->name('quiz_display.screen_state');
Route::get('display/sessions/{quizDisplaySession}/pair', [QuizDisplaySessionController::class, 'pair'])
    ->middleware('signed')
    ->name('quiz_display.pair');
Route::get('display/sessions/{quizDisplaySession}/controller', [QuizDisplaySessionController::class, 'controller'])
    ->name('quiz_display.controller');
Route::get('display/sessions/{quizDisplaySession}/controller-state', [QuizDisplaySessionController::class, 'controllerState'])
    ->name('quiz_display.controller_state');
Route::post('display/sessions/{quizDisplaySession}/answer', [QuizDisplaySessionController::class, 'saveAnswer'])
    ->name('quiz_display.answer');
Route::post('display/sessions/{quizDisplaySession}/navigate', [QuizDisplaySessionController::class, 'navigate'])
    ->name('quiz_display.navigate');
Route::post('display/sessions/{quizDisplaySession}/submit', [QuizDisplaySessionController::class, 'submit'])
    ->name('quiz_display.submit');

// Runtime quiz endpoints remain outside the teacher back office because students may use them anonymously.
Route::prefix('quiz')->name('quiz.')->group(function () {
    Route::get('/join', [QuizParticipantController::class, 'showJoinForm'])->name('join');
    Route::get('/session-conflict', [QuizParticipantController::class, 'showSessionConflict'])->name('session_conflict');
    Route::post('/join', [QuizParticipantController::class, 'validateQuizCode'])
        ->middleware('throttle:' . config('security.throttle.quiz_code_attempts', '10,1'))
        ->name('validate_code');
    Route::get('/join/student', [QuizParticipantController::class, 'showStudentForm'])->name('join_student');
    Route::post('/join/student', [QuizParticipantController::class, 'validateStudentCode'])
        ->middleware('throttle:' . config('security.throttle.student_code_attempts', '5,1'))
        ->name('validate_student');
    Route::get('/start', [QuizParticipantController::class, 'start'])->name('start');
    Route::get('/{quizKey}/start-question', [QuizParticipantController::class, 'startQuestion'])->name('start_question');
    Route::get('/{quizKey}/question/{questionKey}', [QuizParticipantController::class, 'showQuestion'])->name('question');
    Route::post('/{quizKey}/question/{questionKey}/submit', [QuizParticipantController::class, 'submitAnswer'])->name('submit_answer');
    Route::post('/{quizKey}/question/{questionKey}/skip', [QuizParticipantController::class, 'skipQuestion'])->name('skip_question');
    Route::get('/{quizKey}/question/{currentQuestionKey}/next', [QuizParticipantController::class, 'nextQuestion'])->name('next_question');
    Route::post('/{quizKey}/submit', [QuizParticipantController::class, 'submitFinal'])->name('submit_final');
    Route::get('/{quizKey}/end', [QuizParticipantController::class, 'endQuiz'])->name('end');
    Route::post('/force-submit', [QuizAttemptController::class, 'forceSubmit'])->name('force_submit');
});

// Keep framework auth routes in a dedicated file so application routes stay readable.
require __DIR__ . '/auth.php';
