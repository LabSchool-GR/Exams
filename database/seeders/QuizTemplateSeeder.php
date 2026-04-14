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
        ];

        foreach ($templates as $template) {
            QuizTemplate::query()->updateOrCreate(
                ['code' => $template['code']],
                [
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'is_common' => $template['is_common'],
                ]
            );
        }
    }
}
