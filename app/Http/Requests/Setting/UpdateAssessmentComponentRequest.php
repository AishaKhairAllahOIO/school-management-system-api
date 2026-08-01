<?php

namespace App\Http\Requests\Setting;

use App\Models\AssessmentComponent;
use App\Models\GradeSubject;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => ['sometimes', 'integer', 'exists:grade_subjects,id'],
            'type'             => ['sometimes', 'string', 'in:oral,homework,quiz1,quiz2,exam,participation'],
            'name'             => ['sometimes', 'string', 'max:255'],
            'max_mark'         => ['sometimes', 'numeric', 'min:0.5'],
            'weight_percentage' => ['sometimes', 'numeric', 'min:0', 'max:100'],
        ];
    }

public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {

           $componentId = $this->route('id');

            $currentComponent = AssessmentComponent::find($componentId);

            if (!$currentComponent) return;

            $gradeSubjectId = $this->input('grade_subject_id', $currentComponent->grade_subject_id);
            $newMaxMark = $this->has('max_mark') ? (float) $this->input('max_mark') : $currentComponent->max_mark;
            $newWeightPercentage = $this->has('weight_percentage') ? (float) $this->input('weight_percentage') : $currentComponent->weight_percentage;

            $gradeSubject = GradeSubject::find($gradeSubjectId);

            if ($gradeSubject) {

                $existingMarks = AssessmentComponent::where('grade_subject_id', $gradeSubjectId)
                    ->where('id', '!=', $componentId)
                    ->sum('max_mark');

                $totalMarks = $existingMarks + $newMaxMark;

                if ($totalMarks > $gradeSubject->max_mark) {
                    $remainingMark = $gradeSubject->max_mark - $existingMarks;
                    $validator->errors()->add(
                        'max_mark',
                        "Accepted marks ({$totalMarks}) exceed the maximum allowed for this grade subject ({$gradeSubject->max_mark}). Remaining available marks: {$remainingMark}"
                    );
                }

                $existingWeight = AssessmentComponent::where('grade_subject_id', $gradeSubjectId)
                    ->where('id', '!=', $componentId)
                    ->sum('weight_percentage');

                $totalWeight = $existingWeight + $newWeightPercentage;

                if ($totalWeight > 100) {
                    $remainingWeight = 100 - $existingWeight;
                    $validator->errors()->add(
                        'weight_percentage',
                        "The total percentage ({$totalWeight}%) exceeds 100%. The remaining available percentage is: {$remainingWeight}%"
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'grade_subject_id.exists' => 'The selected grade subject does not exist.',
            'type.in' => 'The type must be one of the following: oral, homework, quiz1, quiz2, exam, participation.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'max_mark.min' => 'The max mark must be at least 0.5.',
            'weight_percentage.min' => 'The weight percentage must be at least 0.',
            'weight_percentage.max' => 'The weight percentage may not be greater than 100.',
        ];
    }
}
