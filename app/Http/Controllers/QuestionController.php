<?php

/**
 * QuestionController.php
 *
 * Author: Kanatas Dimitrios (labschool.gr)
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizOwnership;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuestionController extends Controller
{
    use AuthorizesQuizOwnership;

    private function redirectWhenQuizContentLocked(Quiz $quiz): RedirectResponse
    {
        return redirect()
            ->route('quizzes.questions.index', $quiz)
            ->with('error', __('controllers.quiz_content_locked'));
    }

    private function ensureQuizContentUnlocked(Quiz $quiz): ?RedirectResponse
    {
        if (!$quiz->hasLockedContent()) {
            return null;
        }

        return $this->redirectWhenQuizContentLocked($quiz);
    }

    private function canAddQuestion(Quiz $quiz): bool
    {
        $user = auth()->user();

        if (!$user || $user->isAdmin()) {
            return true;
        }

        return $quiz->questions()->count() < $user->max_questions_per_quiz;
    }

    /**
     * Display all questions for a given quiz, including their answers.
     */
    public function index(Quiz $quiz): View
    {
        $this->authorizeQuizAccess($quiz);

        $questions = $quiz->questions()->with('answers')->get();
        $isContentLocked = $quiz->hasLockedContent();
        $canAddQuestion = !$isContentLocked && $this->canAddQuestion($quiz);

        return view('questions.index', compact('quiz', 'questions', 'canAddQuestion', 'isContentLocked'));
    }

    /**
     * Show the form to create a new question for the quiz.
     */
    public function create(Quiz $quiz): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        if (!$this->canAddQuestion($quiz)) {
            return redirect()
                ->route('quizzes.questions.index', $quiz)
                ->with('error', __('controllers.question_limit_reached'));
        }

        return view('questions.create', compact('quiz'));
    }

    /**
     * Store a newly created question together with its answers.
     */
    public function store(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        if (!$this->canAddQuestion($quiz)) {
            return redirect()
                ->route('quizzes.questions.index', $quiz)
                ->with('error', __('controllers.question_limit_reached'));
        }

        [$questionData, $answersData] = $this->validateQuestionPayload($request);

        DB::transaction(function () use ($quiz, $questionData, $answersData): void {
            $question = $quiz->questions()->create($questionData);
            $this->syncAnswers($question, $answersData);
        });

        return redirect()
            ->route('quizzes.questions.index', $quiz)
            ->with('success', __('controllers.question_created_successfully'));
    }

    /**
     * Display a specific question.
     */
    public function show(Quiz $quiz, Question $question): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        return redirect()->route('quizzes.questions.edit', [$quiz, $question]);
    }

    /**
     * Show the form to edit a question and its answers.
     */
    public function edit(Quiz $quiz, Question $question): View|RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        $question->load('answers');

        return view('questions.edit', compact('quiz', 'question'));
    }

    /**
     * Update a question together with its answers.
     */
    public function update(Request $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        [$questionData, $answersData] = $this->validateQuestionPayload($request, $question);

        DB::transaction(function () use ($question, $questionData, $answersData): void {
            $question->update($questionData);
            $this->syncAnswers($question, $answersData);
        });

        return redirect()
            ->route('quizzes.questions.index', $quiz)
            ->with('success', __('controllers.question_updated_successfully'));
    }

    /**
     * Delete the question and its image if present.
     */
    public function destroy(Quiz $quiz, Question $question): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);
        $this->authorizeQuestion($quiz, $question);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        $this->deleteImageIfExists($question->image);
        $question->delete();

        return redirect()
            ->route('quizzes.questions.index', $quiz)
            ->with('success', __('controllers.question_deleted_successfully'));
    }

    /**
     * Ensure that the question belongs to the given quiz.
     */
    private function authorizeQuestion(Quiz $quiz, Question $question): void
    {
        if ($question->quiz_id !== $quiz->id) {
            abort(404);
        }
    }

    /**
     * Delete an image from storage if it exists.
     */
    private function deleteImageIfExists(?string $imagePath): void
    {
        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Validate the unified question editor payload and return normalized data.
     *
     * @return array{0: array<string, mixed>, 1: Collection<int, array{id: int|null, text: string, is_correct: bool}>}
     */
    private function validateQuestionPayload(Request $request, ?Question $question = null): array
    {
        $request->validate([
            'text' => 'required|string',
            'image' => 'nullable|image|max:80',
            'order' => 'nullable|integer',
            'answers' => 'required|array|min:2',
            'answers.*.id' => 'nullable|integer',
            'answers.*.text' => 'nullable|string',
            'answers.*.is_correct' => 'nullable|boolean',
        ], [
            'image.max' => 'The image must not exceed 80KB.',
        ]);

        $answersData = collect($request->input('answers', []))
            ->map(function (array $answer): array {
                return [
                    'id' => isset($answer['id']) ? (int) $answer['id'] : null,
                    'text' => trim((string) ($answer['text'] ?? '')),
                    'is_correct' => (bool) ($answer['is_correct'] ?? false),
                ];
            })
            ->filter(fn (array $answer) => $answer['text'] !== '')
            ->values();

        if ($answersData->count() < 2) {
            throw ValidationException::withMessages([
                'answers' => __('quizzes_cards.minimum_answers_required'),
            ]);
        }

        $user = auth()->user();
        if ($user && !$user->isAdmin() && $answersData->count() > $user->max_answers_per_question) {
            throw ValidationException::withMessages([
                'answers' => __('controllers.answer_limit_reached', [
                    'limit' => $user->max_answers_per_question,
                ]),
            ]);
        }

        if ($answersData->where('is_correct', true)->count() < 1) {
            throw ValidationException::withMessages([
                'answers' => __('quizzes_cards.minimum_correct_answers_required'),
            ]);
        }

        $existingIds = $question
            ? $question->answers()->pluck('id')->map(fn ($id) => (int) $id)->all()
            : [];

        foreach ($answersData as $answerData) {
            if ($answerData['id'] === null) {
                continue;
            }

            if (!in_array($answerData['id'], $existingIds, true)) {
                throw ValidationException::withMessages([
                    'answers' => __('quizzes_cards.invalid_answer_reference'),
                ]);
            }
        }

        $questionData = [
            'text' => $request->text,
            'order' => $request->order,
        ];

        if ($request->hasFile('image')) {
            if ($question?->image) {
                $this->deleteImageIfExists($question->image);
            }

            $questionData['image'] = $request->file('image')->store('questions_images', 'public');
        } elseif ($request->boolean('delete_image') && $question?->image) {
            $this->deleteImageIfExists($question->image);
            $questionData['image'] = null;
        }

        return [$questionData, $answersData];
    }

    /**
     * Update answers in place when possible to preserve existing answer ids.
     */
    private function syncAnswers(Question $question, Collection $answersData): void
    {
        $existingAnswers = $question->answers()->get()->keyBy('id');
        $retainedIds = [];

        foreach ($answersData as $answerData) {
            $answerId = $answerData['id'];

            if ($answerId !== null && $existingAnswers->has($answerId)) {
                $answer = $existingAnswers->get($answerId);
                $answer->update([
                    'text' => $answerData['text'],
                    'is_correct' => $answerData['is_correct'],
                ]);
                $retainedIds[] = $answer->id;
                continue;
            }

            $answer = $question->answers()->create([
                'text' => $answerData['text'],
                'is_correct' => $answerData['is_correct'],
            ]);
            $retainedIds[] = $answer->id;
        }

        $question->answers()
            ->whereNotIn('id', $retainedIds)
            ->get()
            ->each
            ->delete();

        $question->update([
            'correct_answers_count' => $question->answers()->where('is_correct', true)->count(),
        ]);
    }

    /**
     * Import multiple questions from a CSV file.
     *
     * Expected headers: text, answer_1, answer_2, ..., correct_answers
     * Limit: 20 questions per import
     */
    public function importCsv(Request $request, Quiz $quiz): RedirectResponse
    {
        $this->authorizeQuizAccess($quiz);

        if ($redirect = $this->ensureQuizContentUnlocked($quiz)) {
            return $redirect;
        }

        $user = auth()->user();
        if ($user && !$user->isAdmin()) {
            $remainingCapacity = max(0, (int) $user->max_questions_per_quiz - $quiz->questions()->count());

            if ($remainingCapacity < 1) {
                return back()->with('error', __('controllers.question_limit_reached'));
            }
        }

        $request->validate([
            'questions_csv' => 'required|file|mimes:csv,txt|max:1024',
        ]);

        $file = $request->file('questions_csv');
        $lines = array_map('str_getcsv', file($file->getRealPath()));

        if (empty($lines)) {
            return back()->with('error', 'The uploaded CSV is empty.');
        }

        $headers = $this->normalizeQuestionImportHeaders($lines[0]);
        $answerColumns = $this->extractQuestionImportAnswerColumns($headers);
        $headerError = $this->validateQuestionImportHeaders($headers, $answerColumns);

        if ($headerError !== null) {
            return back()->with('error', $headerError);
        }

        $rows = array_slice($lines, 1);
        $parsedRows = [];

        foreach ($rows as $rowIndex => $row) {
            if ($this->isQuestionImportRowEmpty($row)) {
                continue;
            }

            $parsedRow = $this->parseQuestionImportRow($row, $headers, $answerColumns, $rowIndex + 2, $user);

            if (is_string($parsedRow)) {
                return back()->with('error', $parsedRow);
            }

            $parsedRows[] = $parsedRow;
        }

        if (count($parsedRows) > 20) {
            return back()->with('error', 'Maximum of 20 questions allowed per CSV import.');
        }

        if ($user && !$user->isAdmin()) {
            $remainingCapacity = max(0, (int) $user->max_questions_per_quiz - $quiz->questions()->count());

            if (count($parsedRows) > $remainingCapacity) {
                return back()->with('error', __('controllers.question_import_limit_reached', [
                    'remaining' => $remainingCapacity,
                ]));
            }
        }

        if ($parsedRows === []) {
            return back()->with('error', 'No valid questions were imported.');
        }

        DB::transaction(function () use ($quiz, $parsedRows): void {
            foreach ($parsedRows as $parsedRow) {
                $question = $quiz->questions()->create($parsedRow['question']);
                $this->syncAnswers($question, $parsedRow['answers']);
            }
        });

        $created = count($parsedRows);

        return back()->with('success', "$created question(s) imported successfully.");
    }

    /**
     * Normalize CSV headers and strip a potential UTF-8 BOM from the first column.
     *
     * @param  array<int, mixed>  $headers
     * @return array<int, string>
     */
    private function normalizeQuestionImportHeaders(array $headers): array
    {
        return array_map(function ($header): string {
            $header = trim((string) $header);
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;

            return strtolower($header);
        }, $headers);
    }

    /**
     * Locate all answer_N columns and preserve their numeric identifiers.
     *
     * @param  array<int, string>  $headers
     * @return array<int, int>
     */
    private function extractQuestionImportAnswerColumns(array $headers): array
    {
        $answerColumns = [];

        foreach ($headers as $index => $header) {
            if (!preg_match('/^answer_(\d+)$/', $header, $matches)) {
                continue;
            }

            $answerColumns[(int) $matches[1]] = $index;
        }

        ksort($answerColumns);

        return $answerColumns;
    }

    /**
     * Validate the required question import headers.
     *
     * @param  array<int, string>  $headers
     * @param  array<int, int>  $answerColumns
     */
    private function validateQuestionImportHeaders(array $headers, array $answerColumns): ?string
    {
        if (!in_array('text', $headers, true) || !in_array('correct_answers', $headers, true) || count($answerColumns) < 2) {
            return 'CSV must contain the headers: text, answer_1, answer_2, ..., correct_answers.';
        }

        return null;
    }

    /**
     * Skip separator rows that contain no question or answer data.
     *
     * @param  array<int, mixed>  $row
     */
    private function isQuestionImportRowEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Parse a single CSV question row into the same structure used by the editor flow.
     *
     * @param  array<int, mixed>  $row
     * @param  array<int, string>  $headers
     * @param  array<int, int>  $answerColumns
     * @return array{question: array<string, mixed>, answers: Collection<int, array{id: int|null, text: string, is_correct: bool}>}|string
     */
    private function parseQuestionImportRow(array $row, array $headers, array $answerColumns, int $rowNumber, ?User $user): array|string
    {
        $textIndex = array_search('text', $headers, true);
        $correctAnswersIndex = array_search('correct_answers', $headers, true);
        $questionText = trim((string) ($row[$textIndex] ?? ''));

        if ($questionText === '') {
            return "Row {$rowNumber}: question text is required.";
        }

        $answersData = collect($answerColumns)
            ->map(function (int $columnIndex, int $answerNumber) use ($row): array {
                return [
                    'number' => $answerNumber,
                    'text' => trim((string) ($row[$columnIndex] ?? '')),
                ];
            })
            ->filter(fn (array $answer): bool => $answer['text'] !== '')
            ->values();

        if ($answersData->count() < 2) {
            return "Row {$rowNumber}: each question must include at least two answers.";
        }

        if ($user && !$user->isAdmin() && $answersData->count() > $user->max_answers_per_question) {
            return "Row {$rowNumber}: you can import up to {$user->max_answers_per_question} answers per question.";
        }

        $correctAnswersRaw = trim((string) ($row[$correctAnswersIndex] ?? ''));

        if ($correctAnswersRaw === '') {
            return "Row {$rowNumber}: correct_answers is required.";
        }

        $correctAnswerNumbers = collect(explode(',', $correctAnswersRaw))
            ->map(fn (string $value): string => trim($value))
            ->filter(fn (string $value): bool => $value !== '')
            ->values();

        if ($correctAnswerNumbers->isEmpty()) {
            return "Row {$rowNumber}: correct_answers must contain answer numbers like 1 or 1,3.";
        }

        foreach ($correctAnswerNumbers as $correctAnswerNumber) {
            if (!ctype_digit($correctAnswerNumber) || (int) $correctAnswerNumber < 1) {
                return "Row {$rowNumber}: correct_answers must contain answer numbers like 1 or 1,3.";
            }
        }

        $correctAnswerNumbers = $correctAnswerNumbers
            ->map(fn (string $value): int => (int) $value)
            ->values();

        if ($correctAnswerNumbers->unique()->count() !== $correctAnswerNumbers->count()) {
            return "Row {$rowNumber}: correct_answers cannot contain duplicate answer numbers.";
        }

        $availableAnswerNumbers = $answersData->pluck('number')->all();
        $missingAnswerNumbers = array_values(array_diff($correctAnswerNumbers->all(), $availableAnswerNumbers));

        if ($missingAnswerNumbers !== []) {
            return "Row {$rowNumber}: correct_answers references missing answer columns.";
        }

        return [
            'question' => [
                'text' => $questionText,
                'image' => null,
                'correct_answers_count' => 0,
                'order' => null,
            ],
            'answers' => $answersData
                ->map(function (array $answer) use ($correctAnswerNumbers): array {
                    return [
                        'id' => null,
                        'text' => $answer['text'],
                        'is_correct' => $correctAnswerNumbers->contains($answer['number']),
                    ];
                })
                ->values(),
        ];
    }
}
