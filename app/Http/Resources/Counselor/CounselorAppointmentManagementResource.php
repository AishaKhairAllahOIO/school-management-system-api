<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorAppointmentManagementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'appointment_date' =>
                $this->appointment_date?->toDateString(),

            'start_time' => $this->start_time,

            'end_time' => $this->end_time,

            'status' => $this->booking_status,

            'student' => $this->student ? [
                'id' => $this->student->id,

                'name' => $this->student->user
                    ? $this->student->user->first_name
                        . ' '
                        . $this->student->user->last_name
                    : null,

                'photoUrl' => $this->student->user->photo_url
                ? url('/api/documents/photos/' . ltrim(preg_replace('/^.*?(users\/|defaults\/)/', '$1', $this->student->user->photo_url), '/'))
                : null,
            ] : null,
        ];
    }
}