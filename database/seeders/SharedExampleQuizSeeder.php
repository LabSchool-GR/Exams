<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SharedExampleQuizSeeder extends Seeder
{
    private const SYSTEM_KEY = 'art_journey_renaissance_surrealism';

    /**
     * Seed a read-only shared example quiz that teachers can duplicate safely.
     */
    public function run(): void
    {
        $admin = User::query()
            ->where('role', 'admin')
            ->orderBy('id')
            ->first();

        if (!$admin) {
            throw new RuntimeException('Demo seeding requires an admin user. Run "php artisan app:setup-admin" first.');
        }

        $category = Category::firstOrCreate([
            'name' => 'Τέχνη και Πολιτισμός',
        ]);

        $quiz = Quiz::firstOrNew([
            'system_key' => self::SYSTEM_KEY,
        ]);

        if (!$quiz->exists) {
            $quiz->quiz_code = $this->generateUniqueQuizCode();
        }

        $quiz->fill([
            'title' => 'Ταξίδι στην Τέχνη: Από την Αναγέννηση στον Σουρεαλισμό',
            'description' => 'Παράδειγμα δοκιμασίας της εφαρμογής με έμφαση στην ιστορία της τέχνης, έτοιμο για προεπισκόπηση, δοκιμή ως επισκέπτης και αντιγραφή.',
            'category_id' => $category->id,
            'creator_id' => $admin->id,
            'max_attempts' => 1,
            'time_limit' => 20 * 60,
            'is_random_order' => false,
            'is_random_answers_order' => false,
            'show_answer_numbering' => true,
            'allow_guest' => true,
            'has_timer' => true,
            'allow_resume' => false,
            'is_learning_mode' => true,
            'is_certificate_verification_enabled' => false,
            'pass_percentage' => 60,
            'question_view' => 'default',
            'status' => 'active',
            'questions_limit' => null,
            'public_token' => null,
            'public_token_hash' => $quiz->public_token_hash ?: Quiz::generateLinkTokenHash(),
            'is_public' => true,
            'is_anonymous_bulk_mode' => false,
            'is_public_anonymous_pool_mode' => false,
            'anonymous_pool_capacity' => null,
            'student_access_policy' => Quiz::STUDENT_ACCESS_POLICY_PIN_AND_LINKS,
            'language' => 'el',
            'image' => null,
            'is_system_example' => true,
        ]);
        $quiz->save();

        if ($quiz->questions()->exists()) {
            return;
        }

        $questions = [
            [
                'text' => 'Ποιος ζωγράφος δημιούργησε τη «Μόνα Λίζα»;',
                'answers' => [
                    ['text' => 'Πάμπλο Πικάσο', 'is_correct' => false],
                    ['text' => 'Σάντρο Μποτιτσέλι', 'is_correct' => false],
                    ['text' => 'Λεονάρντο ντα Βίντσι', 'is_correct' => true],
                    ['text' => 'Ραφαήλ', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Με ποια πόλη συνδέεται στενά η άνθηση της Αναγέννησης;',
                'answers' => [
                    ['text' => 'Φλωρεντία', 'is_correct' => true],
                    ['text' => 'Βαρκελώνη', 'is_correct' => false],
                    ['text' => 'Άμστερνταμ', 'is_correct' => false],
                    ['text' => 'Βιέννη', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Ποια καλλιτεχνική τεχνική βοήθησε ιδιαίτερα τη ζωγραφική της Αναγέννησης να αποδώσει βάθος;',
                'answers' => [
                    ['text' => 'Η γραμμική προοπτική', 'is_correct' => true],
                    ['text' => 'Η αυτόματη γραφή', 'is_correct' => false],
                    ['text' => 'Το κολάζ', 'is_correct' => false],
                    ['text' => 'Η μεταξοτυπία', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Ποιος καλλιτέχνης φιλοτέχνησε την οροφή της Καπέλα Σιξτίνα;',
                'answers' => [
                    ['text' => 'Μιχαήλ Άγγελος', 'is_correct' => true],
                    ['text' => 'Ντονατέλο', 'is_correct' => false],
                    ['text' => 'Καραβάτζιο', 'is_correct' => false],
                    ['text' => 'Τιτσιάνο', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Ποιο έργο συνδέεται περισσότερο με τον Σαλβαδόρ Νταλί;',
                'answers' => [
                    ['text' => 'Η επιμονή της μνήμης', 'is_correct' => true],
                    ['text' => 'Η Γκερνίκα', 'is_correct' => false],
                    ['text' => 'Το φιλί', 'is_correct' => false],
                    ['text' => 'Η Σχολή των Αθηνών', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Ποιο καλλιτεχνικό ρεύμα έδωσε έμφαση στο όνειρο και το ασυνείδητο;',
                'answers' => [
                    ['text' => 'Ο Σουρεαλισμός', 'is_correct' => true],
                    ['text' => 'Ο Νεοκλασικισμός', 'is_correct' => false],
                    ['text' => 'Ο Ρομαντισμός', 'is_correct' => false],
                    ['text' => 'Ο Ρεαλισμός', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Ποιος ζωγράφος θεωρείται από τις κεντρικές μορφές του κυβισμού;',
                'answers' => [
                    ['text' => 'Πάμπλο Πικάσο', 'is_correct' => true],
                    ['text' => 'Κλοντ Μονέ', 'is_correct' => false],
                    ['text' => 'Γιαν Βερμέερ', 'is_correct' => false],
                    ['text' => 'Ελ Γκρέκο', 'is_correct' => false],
                ],
            ],
            [
                'text' => 'Ποια εφεύρεση συνέβαλε σημαντικά στη διάδοση των ιδεών της Αναγέννησης στην Ευρώπη;',
                'answers' => [
                    ['text' => 'Η τυπογραφία', 'is_correct' => true],
                    ['text' => 'Η φωτογραφία', 'is_correct' => false],
                    ['text' => 'Ο ατμοκινητήρας', 'is_correct' => false],
                    ['text' => 'Ο τηλέγραφος', 'is_correct' => false],
                ],
            ],
        ];

        foreach ($questions as $index => $questionData) {
            $question = $quiz->questions()->create([
                'text' => $questionData['text'],
                'image' => null,
                'correct_answers_count' => 0,
                'order' => $index + 1,
            ]);

            foreach ($questionData['answers'] as $answerData) {
                $question->answers()->create($answerData);
            }
        }
    }

    private function generateUniqueQuizCode(): string
    {
        do {
            $quizCode = str_pad((string) mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Quiz::where('quiz_code', $quizCode)->exists());

        return $quizCode;
    }
}
