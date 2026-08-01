<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...(new UserResource($this))->resolve(),
            'email' => $this?->email,

            'professional_info' => [
                'degree' => $this->staff?->degree,
                'specialization' => $this->staff?->specialization,
                'university' => $this->staff?->university,
                'graduation_year' => $this->staff?->graduation_year,
                'hire_date' => $this->staff?->hire_date,
                'experience_years' => $this->staff?->experience_years,
            ],

            'teacher_assignments' => $this->staff?->teacherAssignments
                    ?->groupBy('class_room_id')
                    ?->map(function ($assignments) {
                        $first = $assignments->first();

                        return [
                            'class_room' => $first->classRoom?->name,
                            'academic_year' => $first->gradeSubject?->academicYear?->year_name,
                            'semester' => $first->gradeSubject?->semester?->semester_name,

                            'subjects' => $assignments->map(fn($a) => $a->gradeSubject?->subject?->subject_name)
                                ->filter()
                                ->unique()
                                ->values()
                                ->toArray(),
                        ];
                    })?->values()?->toArray() ?? [],

            'teacher_workload' => $this->staff?->teacherWorkloads?->map(function ($workload) {
                return [
                    'required_monthly_periods' => $workload->required_monthly_periods,
                    'assigned_monthly_periods' => 7,
                ];
            })->toArray() ?? [],
        ];
    }

}
