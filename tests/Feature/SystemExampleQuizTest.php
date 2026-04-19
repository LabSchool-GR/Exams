<?php

use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;

function makeSystemExampleQuiz(array $quizOverrides = []): array
{
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $category = Category::create([
        'name' => 'System Example Category '.uniqid(),
    ]);

    $quiz = Quiz::create(array_merge([
        'title' => 'Ταξίδι στην Τέχνη: Από την Αναγέννηση στον Σουρεαλισμό',
        'description' => 'Shared platform example.',
        'category_id' => $category->id,
        'creator_id' => $admin->id,
        'quiz_code' => substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8),
        'max_attempts' => 1,
        'time_limit' => 1200,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => true,
        'allow_guest' => true,
        'has_timer' => true,
        'allow_resume' => false,
        'is_learning_mode' => true,
        'pass_percentage' => 60,
        'question_view' => 'default',
        'status' => 'active',
        'questions_limit' => null,
        'is_public' => true,
        'public_token_hash' => Quiz::generateLinkTokenHash(),
        'language' => 'el',
        'is_system_example' => true,
        'system_key' => 'test_example_'.uniqid(),
    ], $quizOverrides));

    $question = Question::create([
        'quiz_id' => $quiz->id,
        'text' => 'Ποιος ζωγράφος δημιούργησε τη «Μόνα Λίζα»;',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);

    $question->answers()->createMany([
        ['text' => 'Πάμπλο Πικάσο', 'is_correct' => false],
        ['text' => 'Λεονάρντο ντα Βίντσι', 'is_correct' => true],
        ['text' => 'Ραφαήλ', 'is_correct' => false],
    ]);

    return [$admin, $quiz];
}

it('shows shared example quizzes in the management index for teachers', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    [, $exampleQuiz] = makeSystemExampleQuiz();

    $ownedCategory = Category::create([
        'name' => 'Owned Category '.uniqid(),
    ]);

    Quiz::create([
        'title' => 'My Own Quiz',
        'description' => 'Owned by the teacher.',
        'category_id' => $ownedCategory->id,
        'creator_id' => $teacher->id,
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

    $this->actingAs($teacher)
        ->get(route('quizzes.index'))
        ->assertOk()
        ->assertSee(__('quizzes.platform_example_badge'))
        ->assertSee($exampleQuiz->title)
        ->assertSee(__('quizzes.try_as_guest'))
        ->assertSee(__('quizzes.copy_as_new_quiz'))
        ->assertSee('My Own Quiz');
});

it('keeps system example quizzes read only for teachers while allowing pdf preview', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
    ]);

    [, $exampleQuiz] = makeSystemExampleQuiz();

    $this->actingAs($teacher)
        ->get(route('quizzes.edit', $exampleQuiz))
        ->assertForbidden();

    $response = $this->actingAs($teacher)
        ->get(route('quizzes.printable_pdf', $exampleQuiz));

    $response->assertOk();
    expect((string) $response->headers->get('content-type'))->toContain('application/pdf');
});

it('duplicates a system example quiz into the teachers own collection', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'max_quizzes' => 5,
    ]);

    [, $exampleQuiz] = makeSystemExampleQuiz();

    $response = $this->actingAs($teacher)
        ->post(route('quizzes.duplicate', $exampleQuiz));

    $copiedQuiz = Quiz::query()
        ->where('creator_id', $teacher->id)
        ->latest('id')
        ->first();

    expect($copiedQuiz)->not->toBeNull();
    expect($copiedQuiz->title)->toBe($exampleQuiz->title);
    expect($copiedQuiz->status)->toBe('inactive');
    expect($copiedQuiz->is_system_example)->toBeFalse();
    expect($copiedQuiz->system_key)->toBeNull();
    expect($copiedQuiz->questions()->count())->toBe($exampleQuiz->questions()->count());
    expect($copiedQuiz->questions()->first()?->answers()->count())->toBe($exampleQuiz->questions()->first()?->answers()->count());

    $response
        ->assertRedirect(route('quizzes.edit', $copiedQuiz))
        ->assertSessionHas('success', __('controllers.quiz_duplicated'));
});

it('still enforces quiz limits when duplicating a system example', function () {
    $teacher = User::factory()->create([
        'role' => 'teacher',
        'max_quizzes' => 0,
    ]);

    [, $exampleQuiz] = makeSystemExampleQuiz();

    $this->actingAs($teacher)
        ->post(route('quizzes.duplicate', $exampleQuiz))
        ->assertRedirect(route('quizzes.index'))
        ->assertSessionHas('error', __('controllers.quiz_limit_reached'));

    expect(Quiz::query()->where('creator_id', $teacher->id)->count())->toBe(0);
});
