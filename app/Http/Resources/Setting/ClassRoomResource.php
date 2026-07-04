<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => (string) $this->id,
            'academicYearId'       => (string) $this->academic_year_id,
            'gradeId'              => (string) $this->grade_level_id, // أو grade_level_id
            'name'                 => $this->name,
            'capacity'             => (int) $this->capacity,
            
            // الحقول المحسوبة آلياً (Accessors)
            'currentStudentsCount' => (int) $this->current_students_count,
            'availableSeats'       => (int) $this->available_seats,
            
            'createdAt'            => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updatedAt'            => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
