<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [

            /*
            |--------------------------------------------------------------------------
            | General / Common Subjects
            |--------------------------------------------------------------------------
            */

            ['name' => 'Bangla', 'category' => 'General'],
            ['name' => 'English', 'category' => 'General'],
            ['name' => 'Mathematics', 'category' => 'General'],
            ['name' => 'ICT', 'category' => 'General'],
            ['name' => 'Bangladesh and Global Studies', 'category' => 'General'],
            ['name' => 'Islamic Studies', 'category' => 'General'],
            ['name' => 'Religion', 'category' => 'General'],

            /*
            |--------------------------------------------------------------------------
            | Science
            |--------------------------------------------------------------------------
            */

            ['name' => 'General Science', 'category' => 'Science'],
            ['name' => 'Physics', 'category' => 'Science'],
            ['name' => 'Chemistry', 'category' => 'Science'],
            ['name' => 'Biology', 'category' => 'Science'],
            ['name' => 'Higher Mathematics', 'category' => 'Science'],

            /*
            |--------------------------------------------------------------------------
            | Business Studies
            |--------------------------------------------------------------------------
            */

            ['name' => 'Accounting', 'category' => 'Business Studies'],
            ['name' => 'Finance', 'category' => 'Business Studies'],
            ['name' => 'Management', 'category' => 'Business Studies'],
            ['name' => 'Business Studies', 'category' => 'Business Studies'],
            ['name' => 'Statistics', 'category' => 'Business Studies'],

            /*
            |--------------------------------------------------------------------------
            | Humanities
            |--------------------------------------------------------------------------
            */

            ['name' => 'Economics', 'category' => 'Humanities'],
            ['name' => 'History', 'category' => 'Humanities'],
            ['name' => 'Geography', 'category' => 'Humanities'],
            ['name' => 'Civics', 'category' => 'Humanities'],
            ['name' => 'Sociology', 'category' => 'Humanities'],
            ['name' => 'Social Science', 'category' => 'Humanities'],
        ];

        foreach ($subjects as $subject) {
            Subject::updateOrCreate(
                [
                    'slug' => Str::slug(
                        $subject['name']
                    ),
                ],
                [
                    'name' => $subject['name'],
                    'category' => $subject['category'],
                    'status' => true,
                ]
            );
        }
    }
}