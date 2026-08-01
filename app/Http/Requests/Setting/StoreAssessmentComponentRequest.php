<?php

namespace App\Http\Requests\Setting;

use App\Models\GradeSubject;
use App\Models\AssessmentComponent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAssessmentComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => ['required', 'integer', 'exists:grade_subjects,id'],
            'type' => ['required', 'string', 'in:oral,homework,quiz1,quiz2,exam,participation'],
            'name' => ['required', 'string', 'max:255'],
            'max_mark' => ['required', 'numeric', 'min:0.5'],
            'weight_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $gradeSubjectId = $this->input('grade_subject_id');
            $newMaxMark = (float) $this->input('max_mark');
            $newWeightPercentage = (float) $this->input('weight_percentage');

            if ($gradeSubjectId) {
                $gradeSubject = GradeSubject::find($gradeSubjectId);

                if ($gradeSubject) {
                    $existingMarks = AssessmentComponent::where('grade_subject_id', $gradeSubjectId)->sum('max_mark');
                    $totalMarks = $existingMarks + $newMaxMark;

                    if ($totalMarks > $gradeSubject->max_mark) {
                        $remainingMark = $gradeSubject->max_mark - $existingMarks;
                        $validator->errors()->add(
                            'max_mark',
                            "accepted marks ({$totalMarks}) exceed the maximum allowed for this grade subject ({$gradeSubject->max_mark}). Remaining available marks: {$remainingMark}"
                        );
                    }

                    $existingWeight = AssessmentComponent::where('grade_subject_id', $gradeSubjectId)->sum('weight_percentage');
                    $totalWeight = $existingWeight + $newWeightPercentage;

                    if ($totalWeight > 100) {
                        $remainingWeight = 100 - $existingWeight;
                        $validator->errors()->add(
                            'weight_percentage',
                            "The total percentage ({$totalWeight}%) exceeds 100%. The remaining available percentage is: {$remainingWeight}%"
                        );
                    }
                }
            }
        });
    }
}
