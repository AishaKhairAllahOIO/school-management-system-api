<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GradeSubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'academicYearId' => (string) $this->academic_year_id,
            'semesterId' => (string) $this->semester_id,
            'gradeId' => (string) $this->grade_level_id,
            'subjectId' => (string) $this->subject_id,

            'subjectName' => $this->whenLoaded('subject', fn() => $this->subject->subject_name),

            'weeklyPeriods' => $this->weekly_periods,
            'difficulty' => $this->difficulty,
            'maxMark' => $this->max_mark,
            'passingMark' => $this->passing_mark,
            'isFailingSubject' => $this->is_failing_subject,
            'weightInTotal' => $this->weight_in_total,
            'maxPeriodsPerDay' => $this->max_periods_per_day,
            'avoidFirstPeriod' => $this->avoid_first_period,
            'avoidLastPeriod' => $this->avoid_last_period,
            'preferredPeriodIndexes' => $this->preferred_period_indexes,

            'createdAt' => $this->created_at->toISOString(),
            'updatedAt' => $this->updated_at->toISOString(),

      ];
    }
}
