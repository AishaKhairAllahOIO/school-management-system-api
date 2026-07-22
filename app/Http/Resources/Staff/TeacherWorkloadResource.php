<?php

namespace App\Http\Resources\Staff;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherWorkloadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'academicYearId'         => $this->academic_year_id,
            'teacherId'              => $this->teacher_id,
            'requiredMonthlyPeriods' => $this->required_monthly_periods,
            'assignedMonthlyPeriods' => $this->assigned_monthly_periods,
            'remainingMonthlyPeriods'=> $this->remaining_monthly_periods,
            'createdAt'              => $this->created_at,
            'updatedAt'              => $this->updated_at,
        ];
    }
}
