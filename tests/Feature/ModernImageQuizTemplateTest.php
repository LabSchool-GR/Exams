<?php

use App\Models\Answer;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizStudent;
use App\Models\QuizTemplate;
use App\Models\User;
use Database\Seeders\QuizTemplateSeeder;
use Illuminate\Support\Collection;

function renderModernImageQuestion(array $answerTexts, ?string $image = null, int $correctAnswersCount = 1): string
{
    $quiz = new Quiz([
        'title' => 'Modern image quiz',
        'description' => 'Adaptive answer layout test',
        'show_answer_numbering' => true,
        'allow_resume' => false,
        'has_timer' => false,
        'time_limit' => 600,
        'pass_percentage' => 50,
        'language' => 'en',
    ]);

    $answers = new Collection;
    foreach ($answerTexts as $index => $text) {
        $answers->push(new Answer([
            'text' => $text,
            'is_correct' => $index < $correctAnswersCount,
        ]));
    }

    $question = new Question([
        'text' => 'Which answer is the most appropriate?',
        'image' => $image,
        'correct_answers_count' => $correctAnswersCount,
        'order' => 1,
    ]);
    $question->setRelation('answers', $answers);

    return view('quiz.templates.modern_img.question', [
        'quiz' => $quiz,
        'question' => $question,
        'quizRouteKey' => 'quiz-route-key',
        'questionRouteKey' => 'question-route-key',
        'questionProgressLabel' => 'Question 1 of 1',
        'currentQuestionNumber' => 1,
        'totalQuestions' => 1,
        'timeRemaining' => 0,
        'allowDisplay' => true,
        'isLastQuestion' => true,
        'isReviewPass' => false,
        'showLearningFeedback' => false,
        'isLearningMode' => false,
    ])->render();
}

test('the core template seeder registers modern image as private and preserves admin visibility changes', function () {
    (new QuizTemplateSeeder)->run();

    $template = QuizTemplate::query()->where('code', 'modern_img')->first();

    expect($template)->not->toBeNull()
        ->and((bool) $template->is_common)->toBeFalse();

    $template->update(['is_common' => true]);
    (new QuizTemplateSeeder)->run();

    expect((bool) $template->fresh()->is_common)->toBeTrue();
});

test('modern image template uses a compact answer grid for short answers', function () {
    $html = renderModernImageQuestion([
        'Athens',
        'Thessaloniki',
        'Patras',
        'Heraklion',
    ]);

    expect($html)
        ->toContain('data-answer-layout="compact"')
        ->toContain('image-answer-list--compact')
        ->toContain('--modern-accent: #176b70')
        ->toContain('clip: rect(0, 0, 0, 0)')
        ->toContain('clip-path: inset(50%)')
        ->toContain('role="status" aria-live="polite" aria-atomic="true"')
        ->not->toContain(__('join.template_media_notice'));
});

test('modern image template keeps long answers in a comfortable single column', function () {
    $html = renderModernImageQuestion([
        'Energy is transferred between systems while the total amount remains conserved.',
        'Energy disappears completely whenever an object stops moving.',
        'Only electrical systems can store or transfer energy.',
        'Temperature and energy are always exactly the same physical quantity.',
    ]);

    expect($html)
        ->toContain('data-answer-layout="comfortable"')
        ->toContain('image-answer-list--comfortable')
        ->not->toContain('class="answer-list image-answer-list--compact"');
});

test('modern image template renders multiple-correct questions as an accessible checkbox group', function () {
    $html = renderModernImageQuestion([
        'First correct answer',
        'Second correct answer',
        'Incorrect answer',
        'Another incorrect answer',
    ], correctAnswersCount: 2);

    expect($html)
        ->toContain('data-correct-count="2"')
        ->toContain('data-instruction-text="'.trans_choice('join.select_instruction', 2, ['count' => 2]).'"')
        ->toContain('type="checkbox" name="answer_id[]"')
        ->not->toContain('type="radio" name="answer_id[]"')
        ->toContain('role="status" aria-live="polite" aria-atomic="true"');
});

test('default image template does not receive modern theme markup', function () {
    $quiz = new Quiz([
        'title' => 'Default image quiz',
        'show_answer_numbering' => true,
        'allow_resume' => false,
        'has_timer' => false,
        'time_limit' => 600,
        'pass_percentage' => 50,
        'language' => 'en',
    ]);
    $question = new Question([
        'text' => 'Default template question',
        'correct_answers_count' => 1,
        'order' => 1,
    ]);
    $question->setRelation('answers', new Collection([
        new Answer(['text' => 'First', 'is_correct' => true]),
        new Answer(['text' => 'Second', 'is_correct' => false]),
    ]));

    $html = view('quiz.templates.default_img.question', [
        'quiz' => $quiz,
        'question' => $question,
        'quizRouteKey' => 'quiz-route-key',
        'questionRouteKey' => 'question-route-key',
        'questionProgressLabel' => 'Question 1 of 1',
        'currentQuestionNumber' => 1,
        'totalQuestions' => 1,
        'timeRemaining' => 0,
        'allowDisplay' => true,
        'isLastQuestion' => true,
        'isReviewPass' => false,
        'showLearningFeedback' => false,
        'isLearningMode' => false,
    ])->render();

    expect($html)
        ->not->toContain('data-answer-layout')
        ->not->toContain('--modern-accent: #176b70')
        ->not->toContain(__('join.template_media_notice'));
});

test('default and modern image templates depend on a neutral shared image layer', function () {
    foreach (['question', 'result', 'start', 'student'] as $screen) {
        $defaultWrapper = file_get_contents(resource_path("views/quiz/templates/default_img/{$screen}.blade.php"));
        $modernWrapper = file_get_contents(resource_path("views/quiz/templates/modern_img/{$screen}.blade.php"));
        $sharedView = file_get_contents(resource_path("views/quiz/templates/shared_img/{$screen}.blade.php"));

        expect($defaultWrapper)
            ->toContain("quiz.templates.shared_img.{$screen}")
            ->not->toContain('quiz.templates.modern_img')
            ->and($modernWrapper)
            ->toContain("quiz.templates.shared_img.{$screen}")
            ->not->toContain('quiz.templates.default_img')
            ->and($sharedView)
            ->not->toContain('quiz.templates.modern_img')
            ->not->toContain('quiz.templates.default_img');
    }
});

test('the internal shared image layer cannot be registered as a selectable template', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this
        ->actingAs($admin)
        ->post(route('quiz_templates.store'), [
            'code' => 'shared_img',
            'name' => 'Internal shared layer',
        ]);

    $response->assertSessionHasErrors('code');
    expect(QuizTemplate::query()->where('code', 'shared_img')->exists())->toBeFalse();
});

test('all modern image participant screens render with the shared visual theme', function () {
    $quiz = new Quiz([
        'title' => 'Modern participant experience',
        'description' => 'A calm modern template with image support.',
        'show_answer_numbering' => true,
        'allow_guest' => true,
        'is_public' => true,
        'public_token' => 'modern-template-public-token',
        'allow_resume' => false,
        'has_timer' => false,
        'time_limit' => 600,
        'pass_percentage' => 50,
        'language' => 'en',
        'image' => 'quiz-images/modern-cover.jpg',
    ]);
    $quiz->id = 8;
    $attempt = new QuizAttempt([
        'student_code' => '0000',
        'student_name' => 'Guest participant',
    ]);

    $studentHtml = view('quiz.templates.modern_img.student', compact('quiz'))->render();
    $startHtml = view('quiz.templates.modern_img.start', [
        'quiz' => $quiz,
        'quizRouteKey' => 'quiz-route-key',
    ])->render();
    $resultHtml = view('quiz.templates.modern_img.result', [
        'quiz' => $quiz,
        'attempt' => $attempt,
        'totalQuestions' => 4,
        'correctCount' => 3,
        'scorePercentage' => 75.0,
        'remainingAttempts' => 0,
        'isLearningModeResult' => false,
    ])->render();
    $defaultResultHtml = view('quiz.templates.default_img.result', [
        'quiz' => $quiz,
        'attempt' => $attempt,
        'totalQuestions' => 4,
        'correctCount' => 3,
        'scorePercentage' => 75.0,
        'remainingAttempts' => 0,
        'isLearningModeResult' => false,
    ])->render();
    $defaultStudentHtml = view('quiz.templates.default_img.student', compact('quiz'))->render();
    $failedResultHtml = view('quiz.templates.modern_img.result', [
        'quiz' => $quiz,
        'attempt' => $attempt,
        'totalQuestions' => 4,
        'correctCount' => 1,
        'scorePercentage' => 25.0,
        'remainingAttempts' => 0,
        'isLearningModeResult' => false,
    ])->render();
    $registeredAttempt = new QuizAttempt([
        'student_code' => '1234',
        'student_name' => 'Registered participant',
    ]);
    $registeredAttempt->id = 99;
    $registeredResultHtml = view('quiz.templates.modern_img.result', [
        'quiz' => $quiz,
        'attempt' => $registeredAttempt,
        'totalQuestions' => 4,
        'correctCount' => 3,
        'scorePercentage' => 75.0,
        'remainingAttempts' => 0,
        'isLearningModeResult' => false,
    ])->render();

    foreach ([$studentHtml, $startHtml, $resultHtml] as $html) {
        expect($html)
            ->toContain('<!DOCTYPE html>')
            ->toContain('--modern-accent: #176b70')
            ->toContain("url('".asset('storage/quiz-images/modern-cover.jpg')."')");
    }

    expect($startHtml)
        ->toContain(__('join.template_media_notice'))
        ->toContain(__('join.template_media_notice_aria'))
        ->toContain('width: 124px')
        ->toContain('stroke: var(--modern-accent)');

    expect($resultHtml)
        ->not->toContain('class="identity-chip"')
        ->not->toContain(__('join.retry_quiz'))
        ->toContain('repeat(auto-fit, minmax(min(100%, 14rem), 1fr))');

    expect($defaultResultHtml)
        ->not->toContain('class="identity-chip"')
        ->not->toContain(__('join.retry_quiz'));

    expect($defaultStudentHtml)
        ->toContain("background-image: url('".asset('storage/quiz-images/modern-cover.jpg')."')");

    expect($failedResultHtml)
        ->toContain(__('join.retry_quiz'));

    expect($registeredResultHtml)
        ->toContain('class="identity-chip"')
        ->toContain('Registered participant');

    foreach ([$studentHtml, $resultHtml] as $html) {
        expect($html)
            ->not->toContain(__('join.template_media_notice'));
    }
});

test('registered participant retry follows remaining attempts and personal-link access', function () {
    $quiz = new Quiz([
        'title' => 'Registered participant retry',
        'pass_percentage' => 50,
        'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS,
    ]);
    $quiz->id = 8;

    $student = new QuizStudent([
        'quiz_id' => 8,
        'student_code' => '1234',
        'student_name' => 'Registered participant',
        'access_token_hash' => str_repeat('a', 64),
    ]);
    $student->id = 77;

    $attempt = new QuizAttempt([
        'quiz_id' => 8,
        'quiz_student_id' => 77,
        'student_code' => '1234',
        'student_name' => 'Registered participant',
    ]);
    $attempt->id = 99;
    $attempt->setRelation('student', $student);

    $viewData = [
        'quiz' => $quiz,
        'attempt' => $attempt,
        'totalQuestions' => 4,
        'correctCount' => 1,
        'scorePercentage' => 25.0,
        'isLearningModeResult' => false,
    ];

    $withRetryHtml = view('quiz.templates.modern_img.result', [
        ...$viewData,
        'remainingAttempts' => 1,
    ])->render();
    $withoutRetryHtml = view('quiz.templates.modern_img.result', [
        ...$viewData,
        'remainingAttempts' => 0,
    ])->render();

    expect($withRetryHtml)
        ->toContain(__('join.retry_quiz'))
        ->toContain('class="identity-chip"')
        ->and($withoutRetryHtml)
        ->not->toContain(__('join.retry_quiz'))
        ->toContain('class="identity-chip"');
});

test('learning mode result hides score download and retry actions', function () {
    $quiz = new Quiz([
        'title' => 'Learning mode result',
        'pass_percentage' => 50,
    ]);
    $quiz->id = 8;

    $attempt = new QuizAttempt([
        'student_code' => '0000',
        'student_name' => 'Guest participant',
    ]);

    $html = view('quiz.templates.modern_img.result', [
        'quiz' => $quiz,
        'attempt' => $attempt,
        'totalQuestions' => 0,
        'correctCount' => 0,
        'scorePercentage' => 0.0,
        'remainingAttempts' => 0,
        'isLearningModeResult' => true,
    ])->render();

    expect($html)
        ->toContain(__('join.learning_mode_result_message'))
        ->not->toContain('class="metrics-panel"')
        ->not->toContain(__('join.download_pdf'))
        ->not->toContain(__('join.pdf_unavailable'))
        ->not->toContain(__('join.retry_quiz'))
        ->toContain(__('join.back_to_home'));
});

test('modern image media notice follows the resolved interface locale', function () {
    $previousLocale = app()->getLocale();

    try {
        app()->setLocale('en');

        $quiz = new Quiz([
            'title' => 'English template notice',
            'allow_guest' => true,
            'language' => 'en',
        ]);

        $html = view('quiz.templates.modern_img.start', [
            'quiz' => $quiz,
            'quizRouteKey' => 'quiz-route-key',
        ])->render();

        expect($html)
            ->toContain('Images and screenshots are displayed at low resolution')
            ->toContain('aria-label="Image usage notice"');
    } finally {
        app()->setLocale($previousLocale);
    }
});
