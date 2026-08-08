<?php

namespace Database\Seeders;

use App\Models\TeacherAssignment;
use Illuminate\Database\Seeder;

class TeacherAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // الشعبة الأولى (class_room_id = 1)
        // ==========================================
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 1], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 2], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 3], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 4], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 5], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 6], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 7], ['teacher_id' => 14]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 8], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 9], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 10], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 11], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 12], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 13], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 1, 'grade_subject_id' => 14], ['teacher_id' => 14]);

        // ==========================================
        // الشعبة الثانية (class_room_id = 2)
        // ==========================================
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 1], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 2], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 3], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 4], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 5], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 6], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 7], ['teacher_id' => 14]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 8], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 9], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 10], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 11], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 12], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 13], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 2, 'grade_subject_id' => 14], ['teacher_id' => 14]);

        // ==========================================
        // الشعبة الثالثة (class_room_id = 3)
        // ==========================================
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 1], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 2], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 3], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 4], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 5], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 6], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 7], ['teacher_id' => 14]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 8], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 9], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 10], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 11], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 12], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 13], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 3, 'grade_subject_id' => 14], ['teacher_id' => 14]);

        // ==========================================
        // الشعبة الرابعة (class_room_id = 4)
        // ==========================================
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 1], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 2], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 3], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 4], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 5], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 6], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 7], ['teacher_id' => 14]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 8], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 9], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 10], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 11], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 12], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 13], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 4, 'grade_subject_id' => 14], ['teacher_id' => 14]);

        // ==========================================
        // الشعبة الخامسة (class_room_id = 5)
        // (حسب توزيعك المخصص في كود الـ SQL)
        // ==========================================
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 1], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 2], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 3], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 4], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 5], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 6], ['teacher_id' => 14]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 7], ['teacher_id' => 8]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 8], ['teacher_id' => 10]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 9], ['teacher_id' => 11]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 10], ['teacher_id' => 12]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 11], ['teacher_id' => 13]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 12], ['teacher_id' => 14]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 13], ['teacher_id' => 1]);
        TeacherAssignment::updateOrCreate(['academic_year_id' => 1, 'semester_id' => 1, 'class_room_id' => 5, 'grade_subject_id' => 14], ['teacher_id' => 8]);
    }
}
