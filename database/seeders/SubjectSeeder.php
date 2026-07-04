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
            'subject_full_mark'=>100,
            'subject_pass_mark'=>50,
            'subject_type'=>'assessment',
        ]);
    }
}
