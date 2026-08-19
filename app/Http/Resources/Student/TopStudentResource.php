<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use App\Support\FileUrl;
use Illuminate\Http\Resources\Json\JsonResource;

class TopStudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $studentId = $this->enrollment->student->id;

        $requestedStudentId = $request->integer('student_id');

        $authStudentId = auth()->user()?->student?->id;

        return [

            'student' => [
                'id' => $studentId,

                'fullName' => trim(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        "{$this->enrollment->student->user->first_name} {$this->enrollment->student->user->father_name} {$this->enrollment->student->user->last_name}"
                    )
                ),

                'photoUrl' => FileUrl::make(
                    $this->enrollment->student->user->photo_url,
                    config('filesystems.public_disk')
                ),

                'isMyChild' => $requestedStudentId
                    ? $studentId === $requestedStudentId
                    : false,

                'isMe' => $authStudentId
                    ? $studentId === $authStudentId
                    : false,
            ],

            'class' => [
                'grade_level' => $this->enrollment->gradeLevel->name ?? null,

                'class_room' => $this->enrollment->classRoom->name ?? null,
            ],

            'results' => [
                'total_marks' => $this->total_marks,

                'max_total_marks' => $this->max_total_marks,

                'percentage' => $this->max_total_marks > 0
                    ? round(
                        ($this->total_marks / $this->max_total_marks) * 100,
                        2
                    ) . '%'
                    : '0%',

                'result' => $this->final_result,
            ],
        ];
    }
}
