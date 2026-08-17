<?php

namespace App\Http\Resources\Counselor;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounselorAppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'date' => $this->appointment_date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->booking_status,
        ];
    }
}