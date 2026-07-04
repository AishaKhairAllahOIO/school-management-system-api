<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeLevelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return  [
            'id'                => (string) $this->id,
            'academicStageId'   => (string) $this->academic_stage_id,
            'name'              => $this->name,
            'level'             => (int) $this->level,
            'isGraduationGrade' => (bool) $this->is_graduation_grade,
            'createdAt'         => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updatedAt'         => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
