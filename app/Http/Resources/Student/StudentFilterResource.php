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
$enrollment = $this;
        $student    = $enrollment->student;
        $user       = $student ? $student->user : null;

        return [
            // المعرفات الأساسية
            'studentId'    => $student ? (string) $student->id : null,
            'userId'       => $student ? (string) $student->user_id : null,
            'guardianId'   => $student ? (string) $student->guardian_id : null,
            'enrollmentId' => (string) $enrollment->id,
            
            'fullName'     =>  $user?->first_name . ' ' . $user?->father_name . ' ' . $user?->last_name,
            
            'grade' => [
                'id'    => (string) $enrollment->grade_level_id,
                'name'  => $enrollment->gradeLevel?->name,
                'level' => $enrollment->gradeLevel?->level,
            ],
            
            // بيانات الشعبة
            'classroom' => [
                'id'    => $enrollment->class_room_id ? (string) $enrollment->class_room_id : null,
                'name'  => $enrollment->classRoom?->name,
            ],
            
            'status' => $enrollment->enrollment_status,

        ];
    }
}