<?php

namespace App\Http\Resources\Web;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        $user = $request->user();
        return [
            'id'          => $this->id,
            'activity_name'        => $this->activity_name,
            'type'        => $this->type,
            'activity_date'        => $this->activity_date,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'description' =>$this->description,
            'created_at' =>$this->created_at->format('Y-m-d-H-i-s'),
            'updated_at' =>$this->updated_at->format('Y-m-d-H-i-s'),

            'scope'       => $this->classRooms->count() > 0 ? 'specific_classrooms' : 'grade_level',

            'grade_level' => [
                'grade_name' => $this->gradeLevel->name,
            ],
            'classrooms'    => $this->classRooms->map(function ($room) {
                return ['class_room_name' => $room->name] ?? null;
            }),

            'is_read' => $user ? $this->readers->contains($user->id) : false,
        ];
    }
}
