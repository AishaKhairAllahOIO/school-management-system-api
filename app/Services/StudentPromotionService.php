<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\GradeLevel;
use App\Models\ClassRoom;
use Illuminate\Support\Facades\DB;

class StudentPromotionService
{
    /**
     * ترفيع الطلاب الناجحين لعام دراسي جديد مع احترام سعة الشعب
     */
    public function promoteStudents($fromAcademicYearId, $toAcademicYearId)
    {
        return DB::transaction(function () use ($fromAcademicYearId, $toAcademicYearId) {
            
            $passedEnrollments = Enrollment::with('gradeLevel')
                ->where('academic_year_id', $fromAcademicYearId)
                ->where('academic_result', 'passed')
                ->get();

            $promotedCount = 0;
            $graduatedCount = 0;
            $skippedCount = 0;
            $promotedWithoutClassCount = 0; // 💡 عداد جديد للطلاب الذين لم يجدوا مقعداً فارغاً

            foreach ($passedEnrollments as $enrollment) {
                
                $currentGrade = $enrollment->gradeLevel;

                if (!$currentGrade) {
                    $skippedCount++;
                    continue;
                }

                if ($currentGrade->is_graduation_grade) {
                    $enrollment->update(['academic_result' => 'passed']);
                    $graduatedCount++;
                    continue;
                }

                $nextGrade = GradeLevel::where('level', $currentGrade->level + 1)->first();

                if (!$nextGrade) {
                    $skippedCount++; 
                    continue;
                }

                $exists = Enrollment::where('student_id', $enrollment->student_id)
                                    ->where('academic_year_id', $toAcademicYearId)
                                    ->exists();
                
                if ($exists) {
                    $skippedCount++;
                    continue;
                }

                // ==========================================
                // 🛑 الحل الجراحي: البحث عن شعبة بها مقاعد شاغرة للعام الجديد
                // ==========================================
                $targetClassRoom = ClassRoom::where('grade_level_id', $nextGrade->id)
                    ->withCount(['enrollments' => function ($query) use ($toAcademicYearId) {
                        // نحسب كم مقعد محجوز في هذه الشعبة (للعام القادم) سواء كان مثبتاً أو معلقاً
                        $query->where('academic_year_id', $toAcademicYearId)
                              ->whereIn('enrollment_status', ['enrolled', 'suspended','completed']);
                    }])
                    ->get()
                    ->first(function ($room) {
                        // نختار أول شعبة يكون فيها سعتها أكبر من عدد المسجلين فيها
                        return $room->capacity > $room->enrollments_count;
                    });

                // إذا لم نجد شعبة فارغة، سيبقى targetClassRoom فارغاً (null)
                if (!$targetClassRoom) {
                    $promotedWithoutClassCount++;
                }

                // 🌟 إنشاء القيد الجديد
                Enrollment::create([
                    'student_id'       => $enrollment->student_id,
                    'academic_year_id' => $toAcademicYearId,
                    'grade_level_id'   => $nextGrade->id,
                    'class_room_id'    => $targetClassRoom?->id, // إذا كانت كل الشعب ممتلئة سيأخذ null بأمان
                    'enrollment_status'=> 'suspended', 
                    'academic_result'  => 'under_study',
                ]);

                $promotedCount++;
            }

            return [
                'promoted_students_count'             => $promotedCount,
                'graduated_students_count'            => $graduatedCount,
                'skipped_students_count'              => $skippedCount,
                'promoted_but_without_class_count'    => $promotedWithoutClassCount, // 💡 إبلاغ الإدارة بالمشكلة بوضوح
            ];
        });
    }
}