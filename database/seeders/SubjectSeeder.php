<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{



    public function run(): void
    {

        $defaultSubjects = [
            'Mathematics',
            'Physics',
            'Chemistry',
            'Arabic',
            'English',
            'Islamic Studies',
            'French',
            'History',
            'Geography',
            'Computer Science',
            'Science',
            'Art',
            'Music',
            'Sports',
        ];

        foreach ($defaultSubjects as $name) {
            Subject::updateOrCreate(['subject_name' => $name]);
        }
    }
}
