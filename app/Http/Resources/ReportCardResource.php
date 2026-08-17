<?php

namespace App\Http\Resources;

use App\Models\Adviser;
use App\Models\GradeConfiguration;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\StudentAttendanceSetting;
use App\Models\StudentAttendance;
use App\Models\User;

class ReportCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // 1. جلب إعدادات الدوام وحساب نسبة الحضور
        $attendanceSetting = StudentAttendanceSetting::where('semester_id', $this->semester_id)->first();
        $totalWorkingDays = $attendanceSetting->total_working_days ?? 90; 

        $unexcusedAbsences = StudentAttendance::where('enrollment_id', $this->enrollment_id)
            ->where('absence_type', 'unexcused')
            ->count();

        $attendancePercentage = $totalWorkingDays > 0 
            ? round((($totalWorkingDays - $unexcusedAbsences) / $totalWorkingDays) * 100, 1) 
            : 100;

        // 💡 2. حساب الحد الأدنى للنجاح (مجموع الحدود الدنيا لكافة المواد في هذا الجلاء)
        $minTotalMarks = $this->details->sum('passing_mark');
        $enrollment = $this->enrollment;
        $gradeName = $enrollment->gradeLevel->name ?? ($enrollment->gradeLevel->grade_name ?? 'غير محدد');
        $className = $enrollment->classRoom->name ?? ($enrollment->classRoom->class_name ?? 'غير محددة');
        
// ج. اسم الموجه/المرشد (من GradeConfiguration الخاص بهذا الصف والسنة الدراسية)
        $gradeConfig = GradeConfiguration::where('grade_level_id', $enrollment->grade_level_id)
            ->where('academic_year_id', $this->academic_year_id)
            ->with('adviser')
            ->first();

        $adviserUser = $gradeConfig?->adviser;
        $adviserName = $adviserUser 
            ? trim(($adviserUser->first_name ?? '') . ' ' . ($adviserUser->last_name ?? ''))
            : 'غير محدد';

        // د. اسم مدير المدرسة (جلب المستخدم ذو الدور manager أو admin)
        $managerUser = User::role(['super_admin'])->first() ;
            
        $managerName = $managerUser 
            ? trim(($managerUser->first_name ?? '') . ' ' . ($managerUser->last_name ?? ''))
            : 'مدير المدرسة';
        return [
            'report_card_id' => $this->id,
            'student_id'     => $this->enrollment->student_id,
            'student_name'   => $this->enrollment->student->user->first_name." ". $this->enrollment->student->user->last_name,
            
           'grade_level'    => $gradeName,
            'class_room'     => $className,
            'supervisor'     => $adviserName,   // اسم الموجه / المرشد المسؤول عن الصف
            'school_manager' => $managerName, 
            'summary' => [
                'total_marks'           => $this->total_marks,
                'min_total_marks'       => $minTotalMarks,              // ✅ الإضافة الجديدة هنا
                'max_total_marks'       => $this->max_total_marks,
                'attendance_status'     => $this->attendance_status,
                'attendance_percentage' => $attendancePercentage . '%', 
                'absences_count'        => $unexcusedAbsences,          
                'final_result'          => $this->final_result,
                'failure_reasons'       => $this->failure_reasons ?? [],
            ],
            
            'subjects' => $this->details->map(function ($detail) {
                return [
                    'subject_name'       => $detail->gradeSubject->subject->subject_name ?? 'مادة',
                    'is_failing_subject' => (bool) $detail->is_failing_subject,
                    'subject_total'      => $detail->subject_total,
                    'max_mark'           => $detail->max_mark,
                    'passing_mark'       => $detail->passing_mark,
                    'status'             => $detail->status,
                    'evaluations'        => json_decode($detail->evaluations_summary, true) ?? [],
                ];
            }),
        ];
    }
}