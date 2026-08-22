<?php

namespace App\Http\Resources\Counselor;

use App\Support\FileUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselingSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->appointment?->student;
        $user = $student?->user;
        return [
            'id' => $this->id,

            'appointment' => [
                'id' => $this->appointment?->id,

                'date' => $this->appointment
                    ?->appointment_date
                        ?->toDateString(),

                'start_time' => $this->appointment?->start_time,

                'end_time' => $this->appointment?->end_time,
            ],

            'student' => $this->appointment?->student ? [
                'id' => $this->appointment->student->id,

                'name' => $this->appointment->student->user
                    ? $this->appointment->student->user->first_name
                    . ' '
                    . $this->appointment->student->user->last_name
                    : null,

                'photoUrl' => $user
                    ? FileUrl::make(
                        $this->appointment->student->user->photo_url,
                        config('filesystems.public_disk')
                    )
                    : null,
            ] : null,

            'attendanceStatus' => $this->attendance_status,

            'assessment' => $this->assessment,

            'notes' => $this->notes,
        ];
    }
}
