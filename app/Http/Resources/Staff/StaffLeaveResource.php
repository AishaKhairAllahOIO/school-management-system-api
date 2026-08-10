<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class StaffLeaveResource extends JsonResource
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
            'staff_id'         => $this->staff_id,
            'leave_type'       => $this->whenLoaded('leaveType', fn() => [
                'id'           => $this->leaveType->id,
                'name'         => $this->leaveType->name,
                'payment_type' => $this->leaveType->payment_type,
            ]),
            'academic_year_id' => $this->academic_year_id,
            'start_date'       => Carbon::parse($this->start_date)->format('Y-m-d'),
            'end_date'         => Carbon::parse($this->end_date)->format('Y-m-d'),
            'created_at'       => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
