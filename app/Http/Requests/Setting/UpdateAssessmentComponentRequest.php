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
                            "مجموع العلامات ({$totalMarks}) يتجاوز العلامة العظمى للمادة ({$gradeSubject->max_mark}). العلامة المتبقية المتاحة هي: {$remainingMark}"
                        );
                    }

                    $existingWeight = AssessmentComponent::where('grade_subject_id', $gradeSubjectId)->sum('weight_percentage');
                    $totalWeight = $existingWeight + $newWeightPercentage;

                    if ($totalWeight > 100) {
                        $remainingWeight = 100 - $existingWeight;
                        $validator->errors()->add(
                            'weight_percentage',
                            "النسبة المئوية الإجمالية ({$totalWeight}%) تتجاوز 100%. النسبة المتبقية المتاحة هي: {$remainingWeight}%"
                        );
                    }
                }
            }
        });
    }
}
