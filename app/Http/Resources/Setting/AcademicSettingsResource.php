<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicSettingsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'                    => (string) $this->id,
            'currentAcademicYearId' => (string) ($this->current_academic_year_id ?? ''),
            'currentSemesterId'     => (string) ($this->current_semester_id ?? ''), // أو current_academic_term_id حسب تسميتك في الداتابيز
            'scheduleSettings'      => $this->schedule_settings,
            'createdAt'             => $this->created_at->toIso8601String(),
            'updatedAt'             => $this->updated_at->toIso8601String(),
        ];
    }
}
