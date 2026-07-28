<?php

use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizTemplate;
use App\Models\User;
use Illuminate\Support\Facades\DB;

test('retired retro template data is removed and existing quizzes move to default image', function () {
    $owner = User::factory()->create([
        'role' => 'teacher',
    ]);
    $category = Category::create([
        'name' => 'Retired Template Migration Category',
    ]);
    $template = QuizTemplate::create([
        'code' => 'retroAXD3_img',
        'name' => 'Retired Retro Template',
        'description' => 'Scheduled for permanent removal',
        'is_common' => false,
    ]);
    $template->users()->sync([$owner->id]);

    $quiz = Quiz::create([
        'title' => 'Legacy Retro Quiz',
        'description' => 'Must keep rendering after the template is removed',
        'category_id' => $category->id,
        'creator_id' => $owner->id,
        'quiz_code' => 'RETRO123',
        'max_attempts' => 1,
        'time_limit' => 600,
        'is_random_order' => false,
        'is_random_answers_order' => false,
        'show_answer_numbering' => true,
        'allow_guest' => false,
        'has_timer' => false,
        'allow_resume' => false,
        'pass_percentage' => 50,
        'question_view' => 'retroAXD3_img',
        'status' => 'active',
        'language' => 'el',
    ]);

    $migration = require database_path('migrations/2026_07_27_120000_remove_retro_axd3_img_template.php');
    $migration->up();

    expect($quiz->fresh()->question_view)->toBe('default_img')
        ->and(QuizTemplate::query()->where('code', 'retroAXD3_img')->exists())->toBeFalse()
        ->and(DB::table('quiz_template_user')->where('quiz_template_id', $template->id)->exists())->toBeFalse()
        ->and(is_dir(resource_path('views/quiz/templates/retroAXD3_img')))->toBeFalse();
});
