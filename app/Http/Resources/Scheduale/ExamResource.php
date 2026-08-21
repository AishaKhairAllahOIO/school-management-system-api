<?php

namespace App\Http\Resources\Scheduale;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'grade_level_id' => $this->grade_level_id,
            'academic_year_id' => $this->academic_year_id,
            'semester_id' => $this->semester_id,

            // نستخدم whenLoaded لمنع مشكلة N+1 Queries إذا لم نقم بجلب العلاقة
            'subjects' => $this->whenLoaded('subjects', function () {
                return $this->subjects->map(function ($examSubject) {
                    return [
                        'exam_subject_id' => $examSubject->id,

                        'subject_id' => $examSubject->gradeSubject->subject->id ?? null,
                        'subject_name' => $examSubject->gradeSubject->subject->subject_name ?? 'Unknown',

                        'exam_date' => Carbon::parse($examSubject->exam_date)->format('Y-m-d'),
                        'start_time' => Carbon::parse($examSubject->start_time)->format('H:i'),
                        'end_time' => Carbon::parse($examSubject->end_time)->format('H:i'),

                        'syllabus' => $examSubject->syllabus,

                        'teachers' => $examSubject->relationLoaded('teachers')
                            ? $examSubject->teachers->map(function ($teacher) {
                                return [
                                    'teacher_id' => $teacher->id,
                                    'teacher_name' => trim(($teacher->user->first_name ?? '') . ' ' . ($teacher->user->last_name ?? '')),
                                ];
                            })
                            : [],
                    ];
                });
            }),

            'created_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d-H-i-s') : null,

        ];
    }
}