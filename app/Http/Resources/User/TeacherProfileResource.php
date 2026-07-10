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
            // جلب البيانات الأساسية للمستخدم
            ...(new \App\Http\Resources\Auth\UserResource($this))->resolve(),

            'professional_info' => [
                'degree'           => $this->staff?->degree,
                'specialization'   => $this->staff?->specialization,
                'university'       => $this->staff?->university,
                'graduation_year'  => $this->staff?->graduation_year,
                'hire_date'        => $this->staff?->hire_date,
                'experience_years' => $this->staff?->experience_years,
            ],

            // 🌟 الحل هنا: استخدام map() للمرور على كل تكليف على حدة
            'teacher_assignments' => $this->staff?->teacherAssignments?->map(function ($assignment) {
                return [
                    // هنا $assignment يمثل تكليفاً واحداً (Model)، لذا يمكننا الآن الوصول لـ subjects
                    'subjects'      => $assignment->subject?->pluck('subject_name')->toArray() ?? [],
                    'academic_year' => $assignment->academicYear?->year_name,
                    'semester'      => $assignment->semester?->semester_name,
                    'class_rooms'   => $assignment->classRoom ? [$assignment->classRoom->name] : [],
                ];
            })->toArray() ?? [],

            'teacher_workload' =>$this->staff?->teacherWorkloads?->map(function($workload){
                 return [
                    'required_monthly_periods' => $workload?->teacherWorkloads?->required_monthly_periods,
                    'assigned_monthly_periods' => 7,
                ];
            })->toArray() ?? [],

        ];
    }
}
