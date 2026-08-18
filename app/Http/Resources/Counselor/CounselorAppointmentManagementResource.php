<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use App\Support\FileUrl;
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

                'photoUrl' => FileUrl::make(
                    $this->student->user->photo_url,
                    config('filesystems.default')
                ),
            ] : null,
        ];
    }
}
