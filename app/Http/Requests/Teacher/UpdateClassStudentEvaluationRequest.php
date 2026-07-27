<?php

namespace App\Http\Requests\Teacher;

use App\ApiResource;
use App\Models\ClassStudentEvaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateClassStudentEvaluationRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        $evaluation = $this->route('evaluation') ?? $this->route('id') ?? $this->route('class_student_evaluation');

        if (!$evaluation instanceof ClassStudentEvaluation) {
            $evaluation = ClassStudentEvaluation::find($evaluation);
        }

        if (!$evaluation) {
            return false;
        }

        $gradeSubjectId = $this->has('grade_subject_id')
            ? (int) $this->input('grade_subject_id')
            : (int) $evaluation->grade_subject_id;

        $enrollmentId = $this->has('enrollment_id')
            ? (int) $this->input('enrollment_id')
            : (int) $evaluation->enrollment_id;

        return $this->user()->can('update', [
            $evaluation,
            $gradeSubjectId,
            $enrollmentId
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('غير مصرح لك بتعديل هذا التقييم، لست الأستاذ الذي أنشأه أو أن الطالب والمادة لم يعودا ضمن نصابك.', 403)
        );
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => ['sometimes', 'required', 'integer', 'exists:grade_subjects,id'],
            'enrollment_id'    => ['sometimes', 'required', 'integer', 'exists:enrollments,id'],
            'rating'           => ['sometimes', 'required', 'string', Rule::in(['excellent', 'very_good', 'good', 'average', 'weak'])],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }
}
