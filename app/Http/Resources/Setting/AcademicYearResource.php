<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AcademicYearResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [           
             'id'        => (string) $this->id,
            'name'      => $this->year_name ?? $this->name,
            'startDate' => Carbon::parse($this->start_date)->format('Y-m-d'),
            'endDate'   => Carbon::parse($this->end_date)->format('Y-m-d'),
            'isCurrent' => (bool) $this->is_current,
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),];
    }
}
