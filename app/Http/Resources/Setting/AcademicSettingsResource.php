<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Http\Resources\Json\JsonResource;


class AcademicSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // جلب جميع السنوات والفصول (بما أن الـ TS Type يطلبها كجزء من الإعدادات)
        $allYears = AcademicYear::all();
        $allTerms = Semester::all();

        return [
            'id'                          => (string) $this->id,
            'currentAcademicYearId'       => (string) $this->current_academic_year_id,
            
            // 1. تشكيل السنوات الدراسية (مع حساب isCurrent ديناميكياً)
            'academicYears'               => $allYears->map(function ($year) {
                return [
                    'id'        => (string) $year->id,
                    'name'      => $year->year_name,
                    'startDate' => $year->start_date,
                    'endDate'   => $year->end_date,
                    // السحر المعماري هنا: الفرونت إند يحصل على isCurrent دون أن نخزنها مرتين
                    'isCurrent' => $year->id === $this->current_academic_year_id, 
                ];
            }),

            // 2. تشكيل الفصول الدراسية (Terms)
            'terms'                       => $allTerms->map(function ($term) {
                return [
                    'id'        => (string) $term->id,
                    'name'      => $term->semester_name,
                    'startDate' => $term->start_date,
                    'endDate'   => $term->end_date,
                    'status'    => $term->status,
                ];
            }),

            // 3. تشكيل سلم العلامات
            'gradeScale'                  => $this->gradeScales->map(function ($scale) {
                return [
                    'id'           => (string) $scale->id,
                    'grade'        => $scale->grade,
                    'minimumScore' => $scale->minimum_score,
                    'maximumScore' => $scale->maximum_score,
                    'description'  => $scale->description ?? "",
                ];
            }),

            // 4. تجميع التفضيلات في كائن واحد (Preferences Object)
            'preferences'                 => [
                'autoPromoteStudents'      => (bool) $this->auto_promote_students,
                'allowStudentRepeating'    => (bool) $this->allow_student_repeating,
                'calculateGpa'             => (bool) $this->calculate_gpa,
                'rankStudents'             => (bool) $this->rank_students,
                'useAttendanceInPromotion' => (bool) $this->use_attendance_in_promotion,
            ],

            // 5. الإعدادات المباشرة
            'passingGrade'                => (string) $this->passing_grade,
            'maximumGrade'                => (int) $this->maximum_grade,
            'gpaScale'                    => (string) $this->gpa_scale,
            'minimumAttendancePercentage' => (int) $this->minimum_attendance_percentage,
            'promotionThreshold'          => (int) $this->promotion_threshold,

            // التواريخ
            'createdAt'                   => $this->created_at->toISOString(),
            'updatedAt'                   => $this->updated_at->toISOString(),
        ];
    }
}
