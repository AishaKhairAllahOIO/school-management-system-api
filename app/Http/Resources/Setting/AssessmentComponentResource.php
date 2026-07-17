<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentComponentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'gradeSubjectId' => (string) $this->grade_subject_id,
            'type' => $this->type,
            'name' => $this->name,
            'maxMark' => $this->max_mark,
            'weightPercentage' => $this->weight_percentage,
            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),
        ];
    }
}
