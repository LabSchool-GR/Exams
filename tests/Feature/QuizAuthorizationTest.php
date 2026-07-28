<?php

/**
 * QuizAuthorizationTest.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizStudent;
use App\Models\QuizTemplate;
use App\Models\User;
use Illuminate\Http\UploadedFile;

function makeQuizForAuthorization(array $ownerOverrides = [], array $quizOverrides = []): array
{
    $owner = User::factory()->create(array_merge([
        'role' => 'teacher',
    ], $ownerOverrides));

    $category = Category::create([
        'name' => 'Quiz Auth Category '.uniqid(),
    ]);

    $quiz = Quiz::create(array_merge([
        'title' => 'Quiz Authorization',
        'description' => 'Security test quiz',
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
    ], $quizOverrides));

    return [$owner, $quiz];
}

it('shows every personal quiz with its creator to administrators', function () {
    [$teacher, $teacherQuiz] = makeQuizForAuthorization(
        ['name' => 'Teacher Quiz Owner'],
        ['title' => 'Teacher Managed Quiz']
    );
    $admin = User::factory()->create([
        'name' => 'Administrator Owner',
        'role' => 'admin',
    ]);

    $adminQuiz = $teacherQuiz->replicate();
    $adminQuiz->fill([
        'title' => 'Administrator Managed Quiz',
        'creator_id' => $admin->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
    ]);
    $adminQuiz->save();

    $this->actingAs($admin)
        ->get(route('quizzes.index'))
        ->assertOk()
        ->assertSee(__('dashboard.all_quizzes_collection'))
        ->assertSee(__('dashboard.all_quizzes_collection_intro'))
        ->assertSee($teacherQuiz->title)
        ->assertSee($adminQuiz->title)
        ->assertSee(__('quizzes.creator_label'))
        ->assertSee($teacher->name)
        ->assertSee(__('quizzes.creator_you'));
});

it('keeps the personal quiz list private and creator metadata hidden from teachers', function () {
    [$teacher, $ownQuiz] = makeQuizForAuthorization(
        ['name' => 'Current Teacher'],
        ['title' => 'Current Teacher Quiz']
    );
    [, $otherQuiz] = makeQuizForAuthorization(
        ['name' => 'Other Teacher'],
        ['title' => 'Other Teacher Quiz']
    );

    $this->actingAs($teacher)
        ->get(route('quizzes.index'))
        ->assertOk()
        ->assertSee(__('dashboard.my_active_quizzes'))
        ->assertSee($ownQuiz->title)
        ->assertDontSee($otherQuiz->title)
        ->assertDontSee(__('quizzes.creator_label'));
});

it('allows the quiz creator to open the quiz edit screen', function () {
    [$owner, $quiz] = makeQuizForAuthorization();

    $this->actingAs($owner)
        ->get(route('quizzes.edit', $quiz))
        ->assertOk();
});

it('allows the admin to open another users quiz edit screen', function () {
    [, $quiz] = makeQuizForAuthorization();

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->get(route('quizzes.edit', $quiz))
        ->assertOk();
});

it('returns 403 when another teacher tries to open the quiz edit screen', function () {
    [, $quiz] = makeQuizForAuthorization();

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($otherTeacher)
        ->get(route('quizzes.edit', $quiz))
        ->assertForbidden();
});

it('allows the quiz creator to update the quiz', function () {
    [$owner, $quiz] = makeQuizForAuthorization();

    $response = $this->actingAs($owner)
        ->put(route('quizzes.update', $quiz), [
            'title' => 'Updated Quiz Title',
            'description' => 'Updated description',
            'category_id' => $quiz->category_id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'notify_creator_on_pass' => '0',
            'status' => 'active',
            'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_PIN_ONLY,
            'language' => 'el',
        ]);

    $response->assertRedirect(route('quizzes.index'));

    expect($quiz->fresh()->title)->toBe('Updated Quiz Title')
        ->and($quiz->fresh()->studentAccessPolicy())->toBe(Quiz::STUDENT_ACCESS_POLICY_PIN_ONLY)
        ->and($quiz->fresh()->shouldNotifyCreatorOnPass())->toBeFalse();
});

it('prevents changing a participation mode while participant records exist', function () {
    [$owner, $quiz] = makeQuizForAuthorization([], [
        'is_anonymous_bulk_mode' => true,
    ]);

    QuizStudent::create([
        'quiz_id' => $quiz->id,
        'student_code' => '0001',
        'student_name' => QuizStudent::examSlotName('0001'),
        'max_attempts' => 1,
        'is_anonymous' => true,
        'access_token_hash' => QuizStudent::generateLinkTokenHash(),
    ]);

    $this->actingAs($owner)
        ->from(route('quizzes.edit', $quiz))
        ->put(route('quizzes.update', $quiz), [
            'title' => $quiz->title,
            'description' => $quiz->description,
            'category_id' => $quiz->category_id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_anonymous_bulk_mode' => '0',
            'is_public_anonymous_pool_mode' => '1',
            'anonymous_pool_capacity' => 10,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.edit', $quiz))
        ->assertSessionHasErrors(['is_anonymous_bulk_mode']);

    expect((bool) $quiz->fresh()->is_anonymous_bulk_mode)->toBeTrue()
        ->and((bool) $quiz->fresh()->is_public_anonymous_pool_mode)->toBeFalse();
});

it('returns 403 when another teacher tries to update the quiz', function () {
    [, $quiz] = makeQuizForAuthorization();

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $this->actingAs($otherTeacher)
        ->put(route('quizzes.update', $quiz), [
            'title' => 'Malicious Update',
            'description' => 'Should fail',
            'category_id' => $quiz->category_id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertForbidden();
});

it('shows the localized image max validation message when creating a quiz', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Create Validation Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Quiz With Large Image',
            'description' => 'Validation should fail',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'status' => 'active',
            'language' => 'el',
            'image' => UploadedFile::fake()->image('large.jpg')->size(200),
        ])
        ->assertSessionHasErrors(['image']);

    expect(session('errors')->first('image'))
        ->toContain('150KB');
});

it('shows the localized image max validation message when updating a quiz', function () {
    [$owner, $quiz] = makeQuizForAuthorization();

    $this->actingAs($owner)
        ->put(route('quizzes.update', $quiz), [
            'title' => 'Quiz With Large Image',
            'description' => 'Validation should fail',
            'category_id' => $quiz->category_id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'status' => 'active',
            'language' => 'el',
            'image' => UploadedFile::fake()->image('large.jpg')->size(200),
        ])
        ->assertSessionHasErrors(['image']);

    expect(session('errors')->first('image'))
        ->toContain('150KB');
});

it('allows the admin to delete another users quiz', function () {
    [, $quiz] = makeQuizForAuthorization();

    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->delete(route('quizzes.destroy', $quiz))
        ->assertRedirect(route('quizzes.index'));

    expect(Quiz::find($quiz->id))->toBeNull();
});

it('locks quiz content changes after attempts exist but still allows status updates', function () {
    [$owner, $quiz] = makeQuizForAuthorization();

    QuizAttempt::create([
        'quiz_id' => $quiz->id,
        'student_code' => '1234',
        'student_name' => 'Locked Participant',
        'score' => 0,
        'status' => QuizAttempt::STATUS_IN_PROGRESS,
        'max_attempts' => 1,
    ]);

    $this->actingAs($owner)
        ->put(route('quizzes.update', $quiz), [
            'title' => 'Changed Title That Must Be Ignored',
            'description' => 'Changed description',
            'category_id' => $quiz->category_id,
            'time_limit' => 99,
            'pass_percentage' => 99,
            'question_view' => 'default',
            'notify_creator_on_pass' => '0',
            'status' => 'inactive',
            'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_LINKS_ONLY,
            'language' => 'en',
        ])
        ->assertRedirect(route('quizzes.index'));

    $quiz->refresh();

    expect($quiz->title)->toBe('Quiz Authorization')
        ->and($quiz->description)->toBe('Security test quiz')
        ->and($quiz->status)->toBe('inactive')
        ->and($quiz->shouldNotifyCreatorOnPass())->toBeFalse()
        ->and($quiz->studentAccessPolicy())->toBe(Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS)
        ->and($quiz->language)->toBe('el');
});

it('allows the quiz creator to disable pass notifications when creating a quiz', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Pass Notification Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Quiet Quiz',
            'description' => 'Do not email on pass',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'notify_creator_on_pass' => '0',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and($createdQuiz->shouldNotifyCreatorOnPass())->toBeFalse();
});

it('stores a public quiz on creation when guest access is enabled', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Public Quiz Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Public Quiz',
            'description' => 'Should be public on create',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'allow_guest' => '1',
            'is_public' => '1',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and((bool) $createdQuiz->allow_guest)->toBeTrue()
        ->and((bool) $createdQuiz->is_public)->toBeTrue()
        ->and($createdQuiz->public_token)->toBeNull()
        ->and($createdQuiz->public_token_hash)->not->toBeNull()
        ->and(strlen($createdQuiz->public_token_hash))->toBe(64);
});

it('forces guest and public access off when anonymous bulk mode is enabled', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Anonymous Bulk Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Anonymous Bulk Quiz',
            'description' => 'Should disable guest/public access',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'allow_guest' => '1',
            'is_public' => '1',
            'is_anonymous_bulk_mode' => '1',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and((bool) $createdQuiz->is_anonymous_bulk_mode)->toBeTrue()
        ->and((bool) $createdQuiz->allow_guest)->toBeFalse()
        ->and((bool) $createdQuiz->is_public)->toBeFalse()
        ->and($createdQuiz->public_token_hash)->toBeNull();
});

it('allows teachers to activate pre-registered bulk exam mode', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'max_students_per_quiz' => 30,
    ]);

    $category = Category::create([
        'name' => 'Teacher Special Mode Category '.uniqid(),
    ]);

    $this->actingAs($teacher)
        ->post(route('quizzes.store'), [
            'title' => 'Teacher Special Mode Quiz',
            'description' => 'Teacher may activate a quota-limited bulk mode',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_anonymous_bulk_mode' => '1',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $teacher->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and((bool) $createdQuiz->is_anonymous_bulk_mode)->toBeTrue()
        ->and((bool) $createdQuiz->is_public_anonymous_pool_mode)->toBeFalse()
        ->and($createdQuiz->anonymous_pool_capacity)->toBeNull();
});

it('allows teachers to activate a public anonymous pool within their participant quota', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'max_students_per_quiz' => 30,
    ]);

    $category = Category::create([
        'name' => 'Teacher Public Pool Category '.uniqid(),
    ]);

    $this->actingAs($teacher)
        ->post(route('quizzes.store'), [
            'title' => 'Teacher Public Pool Quiz',
            'description' => 'Teacher quota-limited public pool',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_public_anonymous_pool_mode' => '1',
            'anonymous_pool_capacity' => 30,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $teacher->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and((bool) $createdQuiz->is_public_anonymous_pool_mode)->toBeTrue()
        ->and((bool) $createdQuiz->is_public)->toBeTrue()
        ->and((bool) $createdQuiz->allow_guest)->toBeFalse()
        ->and($createdQuiz->anonymous_pool_capacity)->toBe(30)
        ->and($createdQuiz->public_token_hash)->not->toBeNull();
});

it('rejects a teacher public anonymous pool capacity above the account quota', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'max_students_per_quiz' => 30,
    ]);

    $category = Category::create([
        'name' => 'Teacher Pool Limit Category '.uniqid(),
    ]);

    $this->actingAs($teacher)
        ->from(route('quizzes.create'))
        ->post(route('quizzes.store'), [
            'title' => 'Oversized Teacher Public Pool',
            'description' => 'Must fail server-side validation',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_public_anonymous_pool_mode' => '1',
            'anonymous_pool_capacity' => 31,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.create'))
        ->assertSessionHasErrors(['anonymous_pool_capacity']);

    expect(Quiz::query()->where('creator_id', $teacher->id)->exists())->toBeFalse();
});

it('shows participation modes to teachers while keeping administrator options hidden', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'max_students_per_quiz' => 30,
    ]);

    $this->actingAs($teacher)
        ->get(route('quizzes.create'))
        ->assertOk()
        ->assertSee(__('quizzes_cards.participation_modes_section'))
        ->assertSee(__('quizzes_cards.anonymous_bulk_mode'))
        ->assertSee(__('quizzes_cards.public_anonymous_pool_mode'))
        ->assertSee(__('quizzes.anonymous_pool_account_limit', ['limit' => 30]))
        ->assertDontSee(__('quizzes_cards.certificate_verification'))
        ->assertDontSee(__('display.mode_label'));
});

it('forces public anonymous pool settings when enabled by an admin', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Public Pool Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Public Pool Quiz',
            'description' => 'Should force pool-safe settings',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'allow_guest' => '1',
            'allow_resume' => '1',
            'is_public_anonymous_pool_mode' => '1',
            'anonymous_pool_capacity' => 100,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and((bool) $createdQuiz->is_public_anonymous_pool_mode)->toBeTrue()
        ->and((bool) $createdQuiz->is_public)->toBeTrue()
        ->and((bool) $createdQuiz->allow_guest)->toBeFalse()
        ->and((bool) $createdQuiz->allow_resume)->toBeFalse()
        ->and($createdQuiz->anonymous_pool_capacity)->toBe(100)
        ->and($createdQuiz->public_token_hash)->not->toBeNull();
});

it('stores learning mode on quiz creation', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Learning Mode Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Learning Mode Quiz',
            'description' => 'Should enable learning mode',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_learning_mode' => '1',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and((bool) $createdQuiz->is_learning_mode)->toBeTrue();
});

it('stores certificate verification only when created by an admin', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Certificate Verification Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Verified Confirmation Quiz',
            'description' => 'Should allow public verification',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_certificate_verification_enabled' => '1',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and($createdQuiz->usesCertificateVerification())->toBeTrue();
});

it('ignores certificate verification when a teacher tries to enable it', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Teacher Certificate Verification Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Teacher Verification Quiz',
            'description' => 'Should not expose public verification',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_certificate_verification_enabled' => '1',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and($createdQuiz->usesCertificateVerification())->toBeFalse();
});

it('rejects learning mode when combined with public anonymous pool mode', function () {
    $owner = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'Learning Mode Conflict Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->from(route('quizzes.create'))
        ->post(route('quizzes.store'), [
            'title' => 'Learning Conflict Quiz',
            'description' => 'Should reject special mode conflict',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'is_learning_mode' => '1',
            'is_public_anonymous_pool_mode' => '1',
            'anonymous_pool_capacity' => 100,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.create'))
        ->assertSessionHasErrors(['is_learning_mode']);
});

it('stores the selected registered student access policy on quiz creation', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Access Policy Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Access Policy Quiz',
            'description' => 'Should store access policy',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_LINKS_ONLY,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertRedirect(route('quizzes.index'));

    $createdQuiz = Quiz::query()->where('creator_id', $owner->id)->latest('id')->first();

    expect($createdQuiz)->not->toBeNull()
        ->and($createdQuiz->studentAccessPolicy())->toBe(Quiz::STUDENT_ACCESS_POLICY_LINKS_ONLY);
});

it('rejects quiz creation when the selected template is not available to the teacher', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $otherTeacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    $privateTemplate = QuizTemplate::create([
        'code' => 'modern_img',
        'name' => 'Private Template',
        'description' => 'Not assigned to the acting teacher',
        'is_common' => false,
    ]);
    $privateTemplate->users()->sync([$otherTeacher->id]);

    $category = Category::create([
        'name' => 'Template Validation Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->get(route('quizzes.create'))
        ->assertOk()
        ->assertDontSee('value="modern_img"', false);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Quiz With Forbidden Template',
            'description' => 'Should fail validation',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => $privateTemplate->code,
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertSessionHasErrors(['question_view']);

    expect(Quiz::query()->where('creator_id', $owner->id)->count())->toBe(0);
});

it('allows an assigned teacher to view and select a private built-in template', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $privateTemplate = QuizTemplate::create([
        'code' => 'modern_img',
        'name' => 'Assigned Modern Template',
        'description' => 'Available only to selected teachers',
        'is_common' => false,
    ]);
    $privateTemplate->users()->sync([$owner->id]);

    $category = Category::create([
        'name' => 'Assigned Template Category '.uniqid(),
    ]);

    $this->actingAs($owner)
        ->get(route('quizzes.create'))
        ->assertOk()
        ->assertSee('value="modern_img"', false);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Quiz With Assigned Template',
            'description' => 'Authorized private template selection',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'modern_img',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertSessionDoesntHaveErrors()
        ->assertRedirect(route('quizzes.index'));

    expect(Quiz::query()
        ->where('creator_id', $owner->id)
        ->where('question_view', 'modern_img')
        ->exists())->toBeTrue();
});

it('does not authorize a non-core template from filesystem presence alone', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Unregistered Template Category '.uniqid(),
    ]);

    expect(QuizTemplate::query()->where('code', 'modern_img')->exists())->toBeFalse();

    $this->actingAs($owner)
        ->get(route('quizzes.create'))
        ->assertOk()
        ->assertDontSee('value="modern_img"', false);

    $this->actingAs($owner)
        ->post(route('quizzes.store'), [
            'title' => 'Quiz With Unregistered Template',
            'description' => 'Filesystem presence must not grant access',
            'category_id' => $category->id,
            'time_limit' => 10,
            'pass_percentage' => 60,
            'question_view' => 'modern_img',
            'status' => 'active',
            'language' => 'el',
        ])
        ->assertSessionHasErrors(['question_view']);
});

it('falls back to the default participant template when the configured template view is missing', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);

    $category = Category::create([
        'name' => 'Fallback Template Category '.uniqid(),
    ]);

    $quiz = Quiz::create([
        'title' => 'Fallback Template Quiz',
        'description' => 'Template fallback quiz',
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
        'question_view' => 'missing_template_code',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => false,
        'language' => 'el',
    ]);

    $this->withSession([
        'attempt_id' => 'guest',
        'student_name' => __('join.guest_name'),
        'quiz_id' => $quiz->id,
    ])
        ->get(route('quiz.start'))
        ->assertOk()
        ->assertViewIs('quiz.templates.default.start');
});
