<?php

namespace Database\Seeders;

use App\Models\QuizTemplate;
use Illuminate\Database\Seeder;

class QuizTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'code' => 'default',
                'name' => '01.Βασικό Πρότυπο Χωρίς Εικόνες Ερωτήσεων',
                'description' => 'Είναι το βασικό πρότυπο χωρίς τη δυνατότητα προσθήκης εικόνων στις ερωτήσεις.',
                'is_common' => true,
            ],
            [
                'code' => 'default_img',
                'name' => '02.Βασικό Πρότυπο Με Εικόνες Ερωτήσεων',
                'description' => 'Είναι το βασικό πρότυπο με τη δυνατότητα προσθήκης εικόνων στις ερωτήσεις.',
                'is_common' => true,
            ],
            [
                'code' => 'modern_img',
                'name' => '03.Σύγχρονο Πρότυπο Με Εικόνες και Προσαρμοζόμενες Απαντήσεις',
                'description' => 'Σύγχρονο και ήρεμο πρότυπο με εικόνες και αυτόματη διάταξη απαντήσεων ανάλογα με το μήκος τους.',
                'is_common' => false,
            ],
        ];

        foreach ($templates as $template) {
            $storedTemplate = QuizTemplate::query()->firstOrNew([
                'code' => $template['code'],
            ]);

            $storedTemplate->name = $template['name'];
            $storedTemplate->description = $template['description'];

            // Seed defaults only on first installation. Re-running the seeder
            // must not override visibility or user assignments chosen by admins.
            if (! $storedTemplate->exists) {
                $storedTemplate->is_common = $template['is_common'];
            }

            $storedTemplate->save();
        }
    }
}
