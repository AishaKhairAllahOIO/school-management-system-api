<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
      return [
            'id'             => $this->id,
            'academicYearId' => $this->academic_year_id,
            'academicTermId' => $this->academic_term_id, // أو semesterId حسب ما يتوقعه الفرونت
            'teacherId'      => $this->teacher_id,
            'gradeSubjectId' => $this->grade_subject_id,
            'classroomId'    => $this->classroom_id, // أصبحت مفردة كما اتفقنا
            'createdAt'      => $this->created_at,
            'updatedAt'      => $this->updated_at,
        ];
     }
}
