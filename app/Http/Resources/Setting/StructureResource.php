<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StructureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'id'               => $this->id,
            'grade_name'       => $this->grade_name,
            'classrooms_count' => $this->class_rooms_count,
            'classrooms'       => $this->classRooms->map(fn($classRoom) => [
                'id'               => $classRoom->id,
                'name'             => $classRoom->name,
                'capacity'         => $classRoom->capacity,
            ]),
        ];
    }
}
