<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::updateOrCreate([
            'subject_name'=>'math',
            'subject_full_mark'=>600,
            'subject_pass_mark'=>300,
            'subject_type'=>'assessment',
        ]);

         Subject::updateOrCreate([
            'subject_name'=>'physic',
            'subject_full_mark'=>400,
            'subject_pass_mark'=>200,
            'subject_type'=>'assessment',
        ]);


    }
}
