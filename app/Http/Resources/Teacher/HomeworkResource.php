<?php

namespace App\Http\Resources\Teacher;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeworkResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'due_date'    => $this->due_date,
            'created_at'  => $this->created_at->format('Y-m-d H:i'),
            'updated_at'  => $this->updated_at->format('Y-m-d H:i'),

            'subject_name'     => $this->gradeSubject?->subject?->subject_name,
            'grade_level_name' => $this->gradeSubject?->gradeLevel?->name,

            'classrooms'  => $this->classRooms->map(function ($room) {
                return [
                    'class_room_id'   => $room->id,
                    'class_room_name' => $room->name,
                ];
            })->toArray(),
            'is_read'          => (bool) ($this->is_read ?? false),
        ];
    }
}
