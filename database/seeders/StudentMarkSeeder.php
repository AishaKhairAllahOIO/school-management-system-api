<?php

namespace Database\Seeders;

use App\Models\StudentMark;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class StudentMarkSeeder extends Seeder
{
  
    public function run(): void
    {
        $enrollmentIds = [1, 2];

        $assessmentComponents = [
            ['id' => 1, 'max_mark' => 30],
            ['id' => 2, 'max_mark' => 60],
            ['id' => 3, 'max_mark' => 60],
            ['id' => 4, 'max_mark' => 60],
            ['id' => 5, 'max_mark' => 30],
            ['id' => 6, 'max_mark' => 360],
        ];

        $teacherId = 1;

        $marksToInsert = [];
        $now = Carbon::now();

        foreach ($enrollmentIds as $enrollmentId) {
            foreach ($assessmentComponents as $component) {

                $randomMark = rand($component['max_mark'] / 2, $component['max_mark']);

                $marksToInsert[] = [
                    'enrollment_id'           => $enrollmentId,
                    'assessment_component_id' => $component['id'],
                    'teacher_id'              => $teacherId,
                    'mark'                    => $randomMark,
                    'notes'                   => 'تم توليد العلامة للتدريب عبر الـ Seeder',
                    'created_at'              => $now,
                    'updated_at'              => $now,
                ];
            }
        }

        StudentMark::insert($marksToInsert);
    }
}
