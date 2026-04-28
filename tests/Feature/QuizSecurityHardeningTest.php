<?php

/**
 * QuizSecurityHardeningTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAnonymousPoolReservation;
use App\Models\QuizAttempt;
use App\Models\QuizDisplaySession;
use App\Models\QuizStudent;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

it('requires a valid signed url for student access links', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Signed Student Link Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Signed Student Link Quiz',
        'description' => 'Student link hardening test',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '4321',
        'student_name' => 'Signed Link Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $bareUrl = route('quizzes.student.link', [
        'student' => $student->id,
        'key' => $student->accessLinkFingerprint(),
    ]);

    // A correct fingerprint alone is not enough; the route must also carry a valid signature.
    $this->get($bareUrl)->assertForbidden();
    $this->get($student->accessLinkUrl(now()->addMinutes(30)))->assertRedirect(route('quiz.start'));
});

it('renders public quiz metadata for messaging link previews', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Preview Metadata Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Preview Metadata Quiz',
        'description' => 'A short description for social previews.',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => true,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'public_token_hash' => Quiz::generateLinkTokenHash(),
        'language' => 'el',
        'image' => 'quizzes_images/preview-metadata.png',
    ]);

    $publicUrl = $quiz->publicAccessUrl(now()->addMinutes(30));

    $this->withHeader('User-Agent', 'Viber')
        ->get($publicUrl)
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Preview Metadata Quiz">', false)
        ->assertSee('<meta property="og:description" content="A short description for social previews.">', false)
        ->assertSee('<meta property="og:url" content="'.e($publicUrl).'">', false)
        ->assertSee('<meta property="og:image" content="'.asset('storage/quizzes_images/preview-metadata.png').'">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
});

it('blocks registered pin access when a quiz allows only personal links', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Links Only Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Links Only Quiz',
        'description' => 'Links-only access policy test',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_LINKS_ONLY,
        'language' => 'el',
    ]);

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '6789',
        'student_name' => 'Links Only Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $quiz->quiz_code,
    ])
        ->assertRedirect(route('quiz.join'))
        ->assertSessionHas('error', __('join.student_pin_access_disabled'))
        ->assertSessionMissing('quiz_id');

    $this->withSession([
        'quiz_id' => $quiz->id,
    ])->post(route('quiz.validate_student'), [
        'student_code' => '6789',
    ])
        ->assertRedirect(route('quiz.join'))
        ->assertSessionHas('error', __('join.student_pin_access_disabled'))
        ->assertSessionMissing('attempt_id');
});

it('blocks personal links when a quiz allows only exam pin access', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Pin Only Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Pin Only Quiz',
        'description' => 'Pin-only access policy test',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_PIN_ONLY,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1357',
        'student_name' => 'Pin Only Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $this->get($student->accessLinkUrl(now()->addMinutes(30)))
        ->assertRedirect(route('quiz.join'))
        ->assertSessionHas('error', __('join.personal_link_access_disabled'))
        ->assertSessionMissing('attempt_id');
});

it('does not write legacy link columns when access urls are rendered', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Legacy Links Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Legacy Links Quiz',
        'description' => 'Legacy link rendering test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => true,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'public_token' => 'legacypublictoken1234567890123456',
        'public_token_hash' => null,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2468',
        'student_name' => 'Legacy Link Student',
        'max_attempts' => 1,
        'access_token' => 'legacystudenttoken12345678901234',
        'access_token_hash' => null,
    ]);

    $publicUrl = $quiz->publicAccessUrl(now()->addMinutes(30));
    $studentUrl = $student->accessLinkUrl(now()->addMinutes(30));

    expect($publicUrl)->not->toBeNull()
        ->and($studentUrl)->not->toBeNull();

    expect(DB::table('quizzes')->where('id', $quiz->id)->value('public_token'))
        ->toBe('legacypublictoken1234567890123456')
        ->and(DB::table('quizzes')->where('id', $quiz->id)->value('public_token_hash'))
        ->toBeNull()
        ->and(DB::table('quiz_students')->where('id', $student->id)->value('access_token'))
        ->toBe('legacystudenttoken12345678901234')
        ->and(DB::table('quiz_students')->where('id', $student->id)->value('access_token_hash'))
        ->toBeNull();
});

it('encrypts student names at rest while keeping admin flows readable', function () {
    /** @var TestCase $this */
    /** @var User $owner */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Encrypted Name Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Encrypted Name Quiz',
        'description' => 'Encrypted name test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '3214',
        'student_name' => 'Encrypted Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 1,
        'score' => 77,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now(),
        'finalized_at' => now(),
    ]);

    $rawStudentName = DB::table('quiz_students')->where('id', $student->id)->value('student_name');
    $rawAttemptName = DB::table('quiz_attempts')->where('id', $attempt->id)->value('student_name');
    $rawStudentBlindIndex = DB::table('quiz_students')->where('id', $student->id)->value('student_name_blind_index');
    $rawAttemptBlindIndex = DB::table('quiz_attempts')->where('id', $attempt->id)->value('student_name_blind_index');

    // Read the raw columns directly to verify encryption at rest rather than relying on the cast layer.
    expect($rawStudentName)->not->toBe('Encrypted Student')
        ->and($rawAttemptName)->not->toBe('Encrypted Student')
        ->and($rawStudentBlindIndex)->not->toBeNull()
        ->and($rawAttemptBlindIndex)->not->toBeNull()
        ->and($rawStudentBlindIndex)->not->toContain('Encrypted Student')
        ->and($rawAttemptBlindIndex)->not->toContain('Encrypted Student');

    $student->refresh();
    $attempt->refresh();

    expect($student->student_name)->toBe('Encrypted Student')
        ->and($attempt->student_name)->toBe('Encrypted Student');

    $this->actingAs($owner)
        ->get(route('quizzes.quiz_attempts.index', $quiz).'?search=Encry')
        ->assertOk()
        ->assertSee('Encrypted Student');

    $this->actingAs($owner)
        ->get(route('quizzes.quiz_attempts.index', $quiz).'?search=Student')
        ->assertOk()
        ->assertSee('Encrypted Student');
});
it('rate limits repeated student code validation attempts', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Student Code Throttle Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Student Code Throttle Quiz',
        'description' => 'Throttle test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    for ($i = 0; $i < 5; $i++) {
        $this->withSession(['quiz_id' => $quiz->id])
            ->post(route('quiz.validate_student'), [
                'student_code' => '9999',
            ])
            ->assertRedirect(route('quiz.join'));
    }

    $this->withSession(['quiz_id' => $quiz->id])
        ->post(route('quiz.validate_student'), [
            'student_code' => '9999',
        ])
        ->assertStatus(429);
});

it('anonymizes old attempts and prunes stale student rows', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Retention Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Retention Quiz',
        'description' => 'Retention test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '6543',
        'student_name' => 'Retention Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
        'created_at' => now()->subDays(400),
        'updated_at' => now()->subDays(400),
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 1,
        'score' => 88,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now()->subDays(400),
        'finalized_at' => now()->subDays(400),
        'created_at' => now()->subDays(400),
        'updated_at' => now()->subDays(400),
    ]);

    // Old records should be anonymized while preserving the attempt row for aggregate analytics.
    Artisan::call('privacy:prune-exam-personal-data', [
        '--attempts-days' => 180,
        '--students-days' => 180,
    ]);

    $attempt->refresh();
    $rawAttemptBlindIndex = DB::table('quiz_attempts')->where('id', $attempt->id)->value('student_name_blind_index');

    expect($attempt->quiz_student_id)->toBeNull()
        ->and($attempt->student_code)->toBe(QuizAttempt::ANONYMIZED_STUDENT_CODE)
        ->and($attempt->student_name)->toBe(QuizAttempt::ANONYMIZED_STUDENT_NAME)
        ->and($rawAttemptBlindIndex)->toBeNull()
        ->and(QuizStudent::find($student->id))->toBeNull();
});

it('keeps recent attempts and active student rows intact during retention pruning', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Recent Retention Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Recent Retention Quiz',
        'description' => 'Recent retention test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '7654',
        'student_name' => 'Recent Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 1,
        'score' => 91,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now()->subDays(10),
        'finalized_at' => now()->subDays(10),
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    // Recent records must remain intact so teachers can still review current participant data.
    Artisan::call('privacy:prune-exam-personal-data', [
        '--attempts-days' => 180,
        '--students-days' => 180,
    ]);

    $attempt->refresh();

    expect($attempt->quiz_student_id)->toBe($student->id)
        ->and($attempt->student_code)->toBe('7654')
        ->and($attempt->student_name)->toBe('Recent Student')
        ->and(QuizStudent::find($student->id))->not->toBeNull();
});

it('prunes stale display sessions and expired anonymous pool reservations', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Temporary Runtime Cleanup Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Temporary Runtime Cleanup Quiz',
        'description' => 'Temporary runtime cleanup test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => true,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
        'is_public_anonymous_pool_mode' => true,
        'anonymous_pool_capacity' => 3,
    ]);

    $student = QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2468',
        'student_name' => 'Temporary Runtime Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $attempt = QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'student_code' => $student->student_code,
        'student_name' => $student->student_name,
        'max_attempts' => 1,
        'score' => 77,
        'status' => QuizAttempt::STATUS_SUBMITTED,
        'submitted_at' => now()->subDays(3),
        'finalized_at' => now()->subDays(3),
    ]);

    $staleDisplaySession = QuizDisplaySession::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'quiz_attempt_id' => $attempt->id,
        'status' => QuizDisplaySession::STATUS_SUBMITTED,
        'state_version' => 2,
        'submitted_at' => now()->subDays(3),
    ]);

    $freshDisplaySession = QuizDisplaySession::create([
        'quiz_id' => $quiz->id,
        'quiz_student_id' => $student->id,
        'quiz_attempt_id' => $attempt->id,
        'status' => QuizDisplaySession::STATUS_ACTIVE,
        'state_version' => 3,
    ]);

    DB::table('quiz_display_sessions')
        ->where('id', $staleDisplaySession->id)
        ->update([
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

    $expiredReservation = QuizAnonymousPoolReservation::create([
        'quiz_id' => $quiz->id,
        'session_id' => 'expired-runtime-session',
        'slot_code' => '1111',
        'expires_at' => now()->subMinute(),
    ]);

    $activeReservation = QuizAnonymousPoolReservation::create([
        'quiz_id' => $quiz->id,
        'session_id' => 'active-runtime-session',
        'slot_code' => '2222',
        'expires_at' => now()->addMinutes(30),
    ]);

    Artisan::call('runtime:prune-temporary-state', [
        '--display-hours' => 48,
    ]);

    expect(QuizDisplaySession::find($staleDisplaySession->id))->toBeNull()
        ->and(QuizDisplaySession::find($freshDisplaySession->id))->not->toBeNull()
        ->and(QuizAnonymousPoolReservation::find($expiredReservation->id))->toBeNull()
        ->and(QuizAnonymousPoolReservation::find($activeReservation->id))->not->toBeNull();
});

it('renders the privacy notice page', function () {
    /** @var TestCase $this */
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee(__('privacy.title'));
});

it('blocks an existing participant session when the quiz is deactivated', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Inactive Session Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Inactive Session Quiz',
        'description' => 'Deactivation flow test quiz',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '2468',
        'student_name' => 'Inactive Session Student',
        'max_attempts' => 1,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $quiz->quiz_code,
    ])->assertRedirect(route('quiz.join_student'));

    $this->post(route('quiz.validate_student'), [
        'student_code' => '2468',
    ])->assertRedirect(route('quiz.start'));

    $quiz->update([
        'status' => 'inactive',
    ]);

    $this->get(route('quiz.start'))
        ->assertRedirect(route('quiz.join'))
        ->assertSessionHas('error', __('join.quiz_inactive'))
        ->assertSessionMissing('quiz_id')
        ->assertSessionMissing('attempt_id');
});

it('shows a friendly conflict page when another quiz replaces the active browser session', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Session Conflict Category '.uniqid(),
    ]);

    $firstQuiz = Quiz::create([
        'title' => 'First Session Quiz',
        'description' => 'First quiz for session conflict test',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => true,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'language' => 'el',
    ]);

    $firstQuestion = $firstQuiz->questions()->create([
        'text' => 'First session question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $firstAnswer = $firstQuestion->answers()->create([
        'text' => 'First correct answer',
        'is_correct' => true,
    ]);

    $firstQuestion->answers()->create([
        'text' => 'First wrong answer',
        'is_correct' => false,
    ]);

    $secondQuiz = Quiz::create([
        'title' => 'Second Session Quiz',
        'description' => 'Second quiz for session conflict test',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => true,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'language' => 'el',
    ]);

    $secondQuestion = $secondQuiz->questions()->create([
        'text' => 'Second session question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $secondQuestion->answers()->create([
        'text' => 'Second correct answer',
        'is_correct' => true,
    ]);

    $secondQuestion->answers()->create([
        'text' => 'Second wrong answer',
        'is_correct' => false,
    ]);

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $firstQuiz->quiz_code,
    ])->assertRedirect(route('quiz.join_student'));

    $this->post(route('quiz.validate_student'), [
        'student_code' => '0000',
    ])->assertRedirect(route('quiz.start'));

    $this->get(route('quiz.start'))->assertOk();

    $firstQuizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $firstQuizKey]))
        ->assertRedirect();

    $firstQuestionKey = array_key_first(session('question_route_map', []));

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $secondQuiz->quiz_code,
    ])->assertRedirect(route('quiz.join_student'));

    $this->post(route('quiz.validate_student'), [
        'student_code' => '0000',
    ])->assertRedirect(route('quiz.start'));

    $this->post(route('quiz.submit_answer', [
        'quizKey' => $firstQuizKey,
        'questionKey' => $firstQuestionKey,
    ]), [
        'current_question_key' => $firstQuestionKey,
        'answer_id' => [$firstAnswer->id],
    ])->assertRedirect(route('quiz.session_conflict'));

    $this->get(route('quiz.session_conflict'))
        ->assertOk()
        ->assertSee(__('join.active_quiz_conflict_title'))
        ->assertSee($secondQuiz->title)
        ->assertSee(__('join.continue_active_quiz'));
});

it('prevents browser caching of participant question pages', function () {
    /** @var TestCase $this */
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'No Store Runtime Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'No Store Runtime Quiz',
        'description' => 'Browser history cache hardening test',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => true,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'language' => 'el',
    ]);

    $question = $quiz->questions()->create([
        'text' => 'No-store question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $question->answers()->create([
        'text' => 'No-store correct answer',
        'is_correct' => true,
    ]);

    $question->answers()->create([
        'text' => 'No-store wrong answer',
        'is_correct' => false,
    ]);

    $this->post(route('quiz.validate_code'), [
        'quiz_code' => $quiz->quiz_code,
    ])->assertRedirect(route('quiz.join_student'));

    $this->post(route('quiz.validate_student'), [
        'student_code' => '0000',
    ])->assertRedirect(route('quiz.start'));

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $quizKey]))
        ->assertRedirect();

    $questionKey = array_key_first(session('question_route_map', []));

    $response = $this->get(route('quiz.question', [
        'quizKey' => $quizKey,
        'questionKey' => $questionKey,
    ]));

    $cacheControl = $response->headers->get('Cache-Control', '');

    $response->assertOk()
        ->assertHeader('Pragma', 'no-cache')
        ->assertHeader('Expires', '0');

    expect($cacheControl)
        ->toContain('no-store')
        ->toContain('no-cache')
        ->toContain('must-revalidate')
        ->toContain('max-age=0')
        ->toContain('private');
});

it('stores public anonymous pool results only after final submission', function () {
    /** @var TestCase $this */
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Public Pool Runtime Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Public Pool Runtime Quiz',
        'description' => 'Public pool runtime test quiz',
        'category_id' => $category->id,
        'creator_id' => $admin->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'is_anonymous_bulk_mode' => false,
        'is_public_anonymous_pool_mode' => true,
        'anonymous_pool_capacity' => 1,
        'public_token_hash' => Quiz::generateLinkTokenHash(),
        'language' => 'el',
    ]);

    $question = $quiz->questions()->create([
        'text' => 'Public pool question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $correctAnswer = $question->answers()->create([
        'text' => 'Correct answer',
        'is_correct' => true,
    ]);

    $question->answers()->create([
        'text' => 'Wrong answer',
        'is_correct' => false,
    ]);

    $this->get($quiz->publicAccessUrl(now()->addMinutes(30)))
        ->assertRedirect(route('quiz.start'));

    expect(QuizStudent::query()->where('quiz_id', $quiz->id)->count())->toBe(0)
        ->and(QuizAttempt::query()->where('quiz_id', $quiz->id)->count())->toBe(0)
        ->and(QuizAnonymousPoolReservation::query()->where('quiz_id', $quiz->id)->count())->toBe(1);

    $this->get(route('quiz.start'))->assertOk();

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.start_question', ['quizKey' => $quizKey]))
        ->assertRedirect();

    $questionKey = array_key_first(session('question_route_map', []));

    $this->post(route('quiz.submit_final', ['quizKey' => $quizKey]), [
        'current_question_key' => $questionKey,
        'answer_id' => [$correctAnswer->id],
    ])->assertRedirect(route('quiz.end', ['quizKey' => $quizKey]));

    $student = QuizStudent::query()->where('quiz_id', $quiz->id)->first();
    $attempt = QuizAttempt::query()->where('quiz_id', $quiz->id)->first();

    expect($student)->not->toBeNull()
        ->and((bool) $student->is_anonymous)->toBeTrue()
        ->and($student->student_code)->not->toBe('0000')
        ->and($student->max_attempts)->toBe(1)
        ->and($attempt)->not->toBeNull()
        ->and($attempt->quiz_student_id)->toBe($student->id)
        ->and($attempt->status)->toBe(QuizAttempt::STATUS_SUBMITTED)
        ->and(QuizAnonymousPoolReservation::query()->where('quiz_id', $quiz->id)->count())->toBe(0);
});

it('drops abandoned public anonymous pool sessions without persisting participants', function () {
    /** @var TestCase $this */
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Public Pool Abandon Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Public Pool Abandon Quiz',
        'description' => 'Public pool abandon test quiz',
        'category_id' => $category->id,
        'creator_id' => $admin->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => false,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'is_anonymous_bulk_mode' => false,
        'is_public_anonymous_pool_mode' => true,
        'anonymous_pool_capacity' => 1,
        'public_token_hash' => Quiz::generateLinkTokenHash(),
        'language' => 'el',
    ]);

    $this->get($quiz->publicAccessUrl(now()->addMinutes(30)))
        ->assertRedirect(route('quiz.start'));

    $quizKey = session('quiz_route_token');

    $this->get(route('quiz.end', ['quizKey' => $quizKey]))->assertOk();

    expect(QuizStudent::query()->where('quiz_id', $quiz->id)->count())->toBe(0)
        ->and(QuizAttempt::query()->where('quiz_id', $quiz->id)->count())->toBe(0)
        ->and(QuizAnonymousPoolReservation::query()->where('quiz_id', $quiz->id)->count())->toBe(0);
});
