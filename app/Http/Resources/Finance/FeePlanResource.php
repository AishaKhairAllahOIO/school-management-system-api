<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Setting\AcademicYearResource;
use App\Http\Resources\Setting\GradeLevelResource;

class FeePlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => (string) $this->id,
            'academicYearId'      => (string) $this->academic_year_id,
            'gradeLevelId'        => (string) $this->grade_level_id,
            
            'name'                => $this->name,
            'baseAmount'          => (float) $this->base_amount,
            
            // العلاقات المحملة (Loaded Relations)
            'academicYear'        => new AcademicYearResource($this->whenLoaded('academicYear')),
            'gradeLevel'          => new GradeLevelResource($this->whenLoaded('gradeLevel')),
            'installmentPolicy'   => new InstallmentPolicyResource($this->whenLoaded('installmentPolicy')),
            'extraServices'       => FeePlanExtraServiceResource::collection($this->whenLoaded('extraServices')),
            
            'createdAt'           => $this->created_at?->toIso8601String(),
            'updatedAt'           => $this->updated_at?->toIso8601String(),
        ];
    }
}