<?php

namespace App\Http\Resources\Leave;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffLeaveTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                         => $this->id,
            'name'                       => $this->name,
            'payment_type'               => $this->payment_type,
            'max_days_per_academic_year' => $this->max_days_per_academic_year,
            'created_at'                 => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
