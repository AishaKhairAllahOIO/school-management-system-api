<?php

namespace App\Http\Resources\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'activity_name'        => $this->activity_name,
            'type'        => $this->type,
            'activity_date'        => $this->activity_date,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            
            'scope'       => $this->class_room_id ? 'classroom' : 'grade_level',

            'grade_level' => [
                'id'   => $this->gradeLevel->id,
                'grade_name' => $this->gradeLevel->grade_name,
            ],
            'classroom'   => $this->whenLoaded('classRoom', fn () =>
                $this->classRoom ? [
                    'id'   => $this->classRoom->id,
                    'name' => $this->classRoom->name,
                ] : null
            ),
        ];
    }
}
