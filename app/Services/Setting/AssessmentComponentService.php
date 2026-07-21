<?php

namespace App\Services\Setting;

use App\Models\AssessmentComponent;
use App\Models\GradeSubject;
use Illuminate\Database\Eloquent\Collection;

class AssessmentComponentService
{
    public function getComponents(?int $gradeSubjectId = null): Collection
    {
        $query = AssessmentComponent::query();

        if ($gradeSubjectId) {
            $query->where('grade_subject_id', $gradeSubjectId);
        }

        // ✅ التعديل هنا: نُرجع المكونات بناءً على الاستعلام، وليس المواد!
        return $query->orderBy('created_at', 'asc')->get();
    }

    public function createComponent(array $data): AssessmentComponent
    {
        return AssessmentComponent::create($data);
    }

    public function updateComponent(AssessmentComponent $component, array $data): AssessmentComponent
    {
        $component->update($data);
        return $component;
    }

    public function deleteComponent(AssessmentComponent $component): void
    {
        $component->delete();
    }

    public function getGroupedSubjectsWithComponents(): Collection
    {
        return GradeSubject::with(['subject', 'assessmentComponents'])
            ->orderBy('grade_level_id')
            ->get();
    }
}
