<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupedAssessmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject_name' => $this->whenLoaded('subject', fn() => $this->subject->subject_name),
            'max_mark' => $this->max_mark,
            'assessment_components' => AssessmentComponentResource::collection($this->whenLoaded('assessmentComponents')),
        ];
    }
}
