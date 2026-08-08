<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'enrollment_id' => $this->enrollment_id,
            'class_room_id' => $this->class_room_id,
            'semester_id' => $this->semester_id,
            'attendance_date' => $this->attendance_date,
            'status' => $this->status,
            'absence_type' => $this->absence_type,
            
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
