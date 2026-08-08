<?php

namespace Database\Seeders;

use App\Models\StudyMaterial;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StudentMaterialsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
          $material1 = StudyMaterial::updateOrCreate(
            [
                'grade_subject_id' => 8,
                'teacher_id'       => 1,
                'title'            => 'ملخص تاريخ - الوحدة الأولى',
                'type'             => 'file',
            ],
            [
                'description'      => 'ملخص الوحدة الأولى في مادة التاريخ مع رسوم توضيحية.',
                'file_path'        => 'materials/2026/history.pdf',
                'original_name'    => 'ملخص تاريخ - الوحدة الأولى.pdf',
                'file_extension'   => 'pdf',
                'file_size'        => 3145728,
                'link_url'         => null,
            ]
        );

        $material2 = StudyMaterial::updateOrCreate(
            [
                'grade_subject_id' => 1,
                'teacher_id'       => 1,
                'title'            => 'مقال علمي - أهمية الرياضيات في حياتنا',
                'type'             => 'link',
            ],
            [
                'description'      => 'مقال علمي يتناول أهمية الرياضيات وتطبيقاتها في الحياة اليومية.',
                'file_path'        => null,
                'original_name'    => null,
                'file_extension'   => null,
                'file_size'        => null,
                'link_url'         => 'https://example.com/math-article',
            ]
        );
    }
}
