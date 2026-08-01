<?php

namespace App\Http\Requests\Teacher;

use App\ApiResource;
use App\Models\ClassStudentEvaluation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreClassStudentEvaluationRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        return $this->user()->can('create', [
            ClassStudentEvaluation::class,
            (int) $this->input('grade_subject_id'),
            (int) $this->input('enrollment_id')
        ]);
    }

    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            $this->errorResponse('You are not authorized to create a class student evaluation.', 403)
        );
    }

    public function rules(): array
    {
        return [
            'grade_subject_id' => ['required', 'integer', 'exists:grade_subjects,id'],
            'enrollment_id'    => ['required', 'integer', 'exists:enrollments,id'],
            'rating'           => ['required', 'string', Rule::in(['excellent', 'very_good', 'good', 'average', 'weak'])],
            'notes'            => ['nullable', 'string', 'max:500'],
        ];
    }
}
