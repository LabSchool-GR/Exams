<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SharedExampleQuizSeeder::class);

        $admin = User::firstOrCreate(
            ['email' => 'admin@quizapp.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ]
        );

        $category = Category::firstOrCreate(['name' => 'Math']);

        $quiz = Quiz::firstOrCreate(
            ['quiz_code' => '12345678'],
            [
                'title' => 'Basic Algebra',
                'description' => 'Simple algebra questions',
                'category_id' => $category->id,
                'creator_id' => $admin->id,
                'max_attempts' => 2,
                'time_limit' => 600,
                'is_random_order' => false,
                'is_random_answers_order' => false,
                'show_answer_numbering' => true,
                'allow_guest' => false,
                'has_timer' => true,
                'allow_resume' => true,
                'pass_percentage' => 50,
                'question_view' => 'default',
                'status' => 'active',
                'language' => 'en',
            ]
        );

        if ($quiz->questions()->exists()) {
            return;
        }

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'text' => 'What is 2 + 2?',
            'correct_answers_count' => 1,
        ]);

        Answer::create([
            'question_id' => $question->id,
            'text' => '3',
            'is_correct' => false,
        ]);

        Answer::create([
            'question_id' => $question->id,
            'text' => '4',
            'is_correct' => true,
        ]);
    }
}
