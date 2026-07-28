<?php

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizStudent;
use App\Models\User;

function anonymousPdfQuiz(): Quiz
{
    $quiz = new Quiz([
        'title' => 'Pre-registered Exam Slots Quiz',
        'description' => 'Printable exam-slot mapping.',
        'quiz_code' => 'ABCD1234',
        'status' => 'active',
        'has_timer' => false,
        'allow_resume' => false,
        'is_anonymous_bulk_mode' => true,
    ]);
    $quiz->id = 8;
    $quiz->setRelation('creator', new User(['name' => 'Quiz Teacher']));

    return $quiz;
}

test('individual student sheet leaves a handwritten name line only for pre-registered exam slots', function () {
    $quiz = anonymousPdfQuiz();
    $anonymousStudent = new QuizStudent([
        'student_code' => '0001',
        'student_name' => QuizStudent::examSlotName('0001'),
        'max_attempts' => 1,
        'is_anonymous' => true,
    ]);
    $registeredStudent = new QuizStudent([
        'student_code' => '1001',
        'student_name' => 'Registered Student',
        'max_attempts' => 1,
        'is_anonymous' => false,
    ]);
    $viewData = [
        'quiz' => $quiz,
        'join_url' => 'https://example.test/exams',
        'student_url' => 'https://example.test/exams/student',
        'guest_url' => null,
        'pin_join_url' => 'https://example.test/exams',
        'qr_svg' => base64_encode('<svg></svg>'),
        'is_guest' => false,
        'show_pin_access' => true,
        'show_personal_link' => true,
    ];

    $anonymousHtml = view('quiz_attempts.student_info_pdf', [
        ...$viewData,
        'student' => $anonymousStudent,
    ])->render();
    $registeredHtml = view('quiz_attempts.student_info_pdf', [
        ...$viewData,
        'student' => $registeredStudent,
    ])->render();

    expect($anonymousHtml)
        ->toContain('class="handwrite-line"')
        ->not->toContain(QuizStudent::examSlotName('0001'))
        ->toContain('0001')
        ->and(substr_count($anonymousHtml, __('pdfexp.qr_label')))->toBe(1)
        ->and($registeredHtml)
        ->toContain('Registered Student')
        ->not->toContain('<span class="handwrite-line">&nbsp;</span>');
});

test('student register keeps real names and replaces exam-slot labels with handwritten lines', function () {
    $html = view('quiz_attempts.students_report_pdf', [
        'quiz' => anonymousPdfQuiz(),
        'data' => collect([
            [
                'name' => QuizStudent::examSlotName('0001'),
                'code' => '0001',
                'max_attempts' => 1,
                'is_anonymous' => true,
            ],
            [
                'name' => 'Registered Student',
                'code' => '1001',
                'max_attempts' => 2,
                'is_anonymous' => false,
            ],
        ]),
    ])->render();

    expect($html)
        ->toContain('class="sheet"')
        ->toContain('class="students-table"')
        ->toContain('<span class="handwrite-line">&nbsp;</span>')
        ->not->toContain(QuizStudent::examSlotName('0001'))
        ->toContain('Registered Student')
        ->toContain('0001')
        ->toContain('1001');
});

test('exam-slot QR cards provide a handwritten name line without carrying a placeholder name', function () {
    $html = view('quiz_attempts.anonymous_cards_pdf', [
        'quiz' => anonymousPdfQuiz(),
        'cards' => collect([
            [
                'student_code' => '0001',
                'max_attempts' => 1,
                'student_url' => 'https://example.test/exams/student',
                'qr_svg' => null,
            ],
        ]),
    ])->render();

    expect($html)
        ->toContain('table-layout: fixed')
        ->toContain('class="card-cell"')
        ->toContain('class="handwrite-line"')
        ->toContain('&#8203;')
        ->not->toContain(QuizStudent::examSlotName('0001'))
        ->toContain('0001');
});

test('legacy anonymous placeholders are presented as exam slots only in pre-registered bulk mode', function () {
    $student = new QuizStudent([
        'student_code' => '0001',
        'student_name' => __('controllers.anonymous_student_name'),
        'is_anonymous' => true,
    ]);
    $bulkQuiz = anonymousPdfQuiz();
    $publicPoolQuiz = new Quiz([
        'is_anonymous_bulk_mode' => false,
        'is_public_anonymous_pool_mode' => true,
    ]);

    expect($student->displayName($bulkQuiz))
        ->toBe(QuizStudent::examSlotName('0001'))
        ->and($student->displayName($publicPoolQuiz))
        ->toBe(__('controllers.anonymous_student_name'));
});

test('certificate leaves a handwritten name line only for anonymous participants', function () {
    $quiz = anonymousPdfQuiz();
    $quiz->setAttribute('is_certificate_verification_enabled', false);

    $anonymousAttempt = new QuizAttempt([
        'student_name' => __('controllers.anonymous_student_name'),
        'score' => 100,
        'submitted_at' => now(),
    ]);
    $anonymousAttempt->id = 2;

    $registeredAttempt = new QuizAttempt([
        'student_name' => 'Registered Student',
        'score' => 100,
        'submitted_at' => now(),
    ]);
    $registeredAttempt->id = 3;

    $anonymousHtml = view('quiz_attempts.certificate', [
        'attempt' => $anonymousAttempt,
        'quiz' => $quiz,
        'isAnonymousParticipant' => true,
    ])->render();
    $registeredHtml = view('quiz_attempts.certificate', [
        'attempt' => $registeredAttempt,
        'quiz' => $quiz,
        'isAnonymousParticipant' => false,
    ])->render();

    expect($anonymousHtml)
        ->toContain('class="certificate-name-line"')
        ->not->toContain(__('controllers.anonymous_student_name'))
        ->and($registeredHtml)
        ->toContain('<strong>Registered Student</strong>')
        ->not->toContain('class="certificate-name-line"');
});

test('result PDF leaves a handwritten name line only for anonymous participants', function () {
    $quiz = anonymousPdfQuiz();
    $quiz->setRelation('questions', collect());

    $anonymousAttempt = new QuizAttempt([
        'student_code' => '0002',
        'student_name' => __('controllers.anonymous_student_name'),
        'score' => 100,
        'submitted_at' => now(),
    ]);
    $anonymousAttempt->id = 2;

    $registeredAttempt = new QuizAttempt([
        'student_code' => '1002',
        'student_name' => 'Registered Student',
        'score' => 100,
        'submitted_at' => now(),
    ]);
    $registeredAttempt->id = 3;

    $viewData = [
        'quiz' => $quiz,
        'groupedAnswersByQuestion' => collect(),
        'questionResults' => [],
        'correctAnswersMap' => [],
        'scorePercentage' => 100,
    ];

    $anonymousHtml = view('quiz_attempts.result_pdf', [
        ...$viewData,
        'attempt' => $anonymousAttempt,
        'isAnonymousParticipant' => true,
    ])->render();
    $registeredHtml = view('quiz_attempts.result_pdf', [
        ...$viewData,
        'attempt' => $registeredAttempt,
        'isAnonymousParticipant' => false,
    ])->render();

    expect($anonymousHtml)
        ->toContain('class="result-name-line"')
        ->not->toContain(__('controllers.anonymous_student_name'))
        ->toContain('0002')
        ->and($registeredHtml)
        ->toContain('Registered Student')
        ->not->toContain('class="result-name-line"');
});
