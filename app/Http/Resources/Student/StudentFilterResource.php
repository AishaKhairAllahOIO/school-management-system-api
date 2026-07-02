<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFilterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $enrollment = $this->enrollments->first(); 

        return [
            'studentId'     => $this->id,
            'userId'        => $this->user_id,
            'guardianId'    => $this->guardian_id,
            'enrollmentId'  => $enrollment ? (string)$enrollment->id : null,
            
            // الاسم الثلاثي
            'fullName'      => $this->user->first_name . ' ' . 
                               $this->user->father_name . ' ' . 
                               $this->user->last_name,
            
            // بيانات الصف والشعبة
            'grade' => [
                'id'   => $enrollment ? (string)$enrollment->grade_level_id : null,
                'name' => $enrollment ? $enrollment->gradeLevel->name : null,
            ],
            'classroom' => [
                'id'   => $enrollment ? (string)$enrollment->class_room_id : null,
                'name' => $enrollment ? $enrollment->classRoom->name : null,
            ],
        ];
    }
}
