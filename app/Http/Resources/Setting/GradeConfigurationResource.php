<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeConfigurationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return[
            'id'                      => (string) $this->id,
            'academicYearId'          => (string) $this->academic_year_id,
            'gradeId'                 => (string) $this->grade_level_id, // أو grade_level_id حسب تسميتك النهائية في الداتابيز
            'supervisorId'            => $this->supervisor_id ? (string) $this->supervisor_id : null,
            
            // السعات المخطط لها
            'plannedClassroomsCount'  => (int) $this->planned_classrooms_count,
            'plannedStudentsCapacity' => (int) $this->planned_students_capacity,
            
            // الحقول المحسوبة آلياً (Accessors)
            'actualClassroomsCount'   => (int) $this->actual_classrooms_count,
            'actualStudentsCount'     => (int) $this->actual_students_count,
            
            'createdAt'               => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updatedAt'               => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
