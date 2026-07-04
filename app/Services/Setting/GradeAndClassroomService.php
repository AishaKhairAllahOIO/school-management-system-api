<?php

namespace App\Services\Setting;

use App\Models\GradeLevel;
use App\Models\GradeConfiguration;
use App\Models\Classroom;
use Illuminate\Support\Facades\DB;

class GradeAndClassroomService
{
    /**
     * العقل المدبر: استنتاج المستوى الدراسي (Level) من الاسم آلياً
     * هذه الدالة تحمي النظام من التضارب إذا نسي المستخدم إدخال المستوى الصحيح.
     */
    private function determineLevelFromName(string $name): int
    {
        // فحص الثانوي أولاً (حتى لا يتداخل 'ثاني عشر' مع 'ثاني')

        
        // فحص بقية الصفوف
        if (str_contains($name, 'أول')) return 1;
        if (str_contains($name, 'ثاني')) return 2;
        if (str_contains($name, 'ثالث')) return 3;

        
        return 1; // قيمة افتراضية في حال تم إدخال اسم غريب
    }

    // =====================================================================
    // --- عمليات الصفوف (Grades) ---
    // =====================================================================
    
    public function createGrade(array $data)
    {
        $name = $data['name'];
        $level = $this->determineLevelFromName($name); // 👈 استنتاج آلي

        return GradeLevel::create([
            'academic_stage_id'   => $data['academicStageId'],
            'name'                => $name,
            'level'               => $level, // تم التوليد والإجبار آلياً
            'is_graduation_grade' => $data['isGraduationGrade'] ?? false,
        ]);
    }

    public function updateGrade(GradeLevel $grade, array $data)
    {
        $name = $data['name'] ?? $grade->name;
        $level = $this->determineLevelFromName($name); // 👈 تحديث آلي عند تغير الاسم

        $grade->update([
            'academic_stage_id'   => $data['academicStageId'] ?? $grade->academic_stage_id,
            'name'                => $name,
            'level'               => $level, // يتحدث آلياً
            'is_graduation_grade' => $data['isGraduationGrade'] ?? $grade->is_graduation_grade,
        ]);

        return $grade->fresh();
    }
    
    // =====================================================================
    // --- عمليات التكوين التخطيطي (Grade Configuration) ---
    // =====================================================================

    public function createConfiguration(array $data)
    {
        return GradeConfiguration::create([
            'academic_year_id'         => $data['academicYearId'],
            'grade_level_id'           => $data['grade_level_id'],
            'supervisor_id'            => $data['supervisor_id'] ?? null,
            'planned_classrooms_count' => $data['planned_classrooms_count'],
        ]);
    }

    public function updateConfiguration(GradeConfiguration $config, array $data)
    {
        // 🛡️ حماية: نمنع تحديث (السنة والصف) لأنهما مفتاح الاستعلام الأساسي
        $config->update([
            'grade_level_id'           => $data['grade_level_id'] ?? $config->grade_level_id,
            'supervisor_id'            => $data['supervisor_id'] ?? $config->supervisor_id,
            'planned_classrooms_count' => $data['planned_classrooms_count'] ?? $config->planned_classrooms_count,
        ]);

        return $config->fresh();
    }

    // =====================================================================
    // --- عمليات الشعب الدراسية (Classrooms) ---
    // =====================================================================

    public function createClassroom(array $data)
    {
        return DB::transaction(function () use ($data) {
            $yearId = $data['academicYearId'];
            $gradeId = $data['grade_level_id'];
            $capacity = $data['capacity'];

            // ✨ السحر هنا: التوليد التلقائي لاسم الشعبة عند الإنشاء!
            $currentCount = Classroom::where('academic_year_id', $yearId)
                                     ->where('grade_level_id', $gradeId)
                                     ->count();
                                     
            $name = "الشعبة " . ($currentCount + 1);

            $classroom = Classroom::create([
                'academic_year_id' => $yearId,
                'grade_level_id'   => $gradeId,
                'name'             => $name,
                'capacity'         => $capacity,
            ]);

            // تحديث السعة التخطيطية الكلية في جدول GradeConfiguration 
            $this->recalculateGradeCapacity($yearId, $gradeId);

            return $classroom;
        });
    }

    public function updateClassroom(Classroom $classroom, array $data)
    {
        return DB::transaction(function () use ($classroom, $data) {
            $classroom->update([
                'grade_level_id' => $data['grade_level_id'] ?? $classroom->grade_level_id,
                'capacity' => $data['capacity'] ?? $classroom->capacity,
            ]);

            // إعادة الحساب لأن السعة ربما تغيرت (مثلاً من 30 إلى 35)
            $this->recalculateGradeCapacity($classroom->academic_year_id, $classroom->grade_level_id);

            return $classroom->fresh();
        });
    }

    // =====================================================================
    // --- دوال مساعدة داخلية (Helpers) ---
    // =====================================================================

    /**
     * إعادة حساب السعة الكلية للصف كلما تمت إضافة/تعديل سعة شعبة
     */
    private function recalculateGradeCapacity($yearId, $gradeId): void 
    {
        $totalCapacity = Classroom::where('academic_year_id', $yearId)
                                  ->where('grade_level_id', $gradeId)
                                  ->sum('capacity');
                                  
        GradeConfiguration::where('academic_year_id', $yearId)
                          ->where('grade_level_id', $gradeId)
                          ->update(['planned_students_capacity' => $totalCapacity]);
    }
}