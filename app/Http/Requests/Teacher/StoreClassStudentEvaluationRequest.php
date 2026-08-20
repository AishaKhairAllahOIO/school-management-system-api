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
    public function messages(): array
    {
        return [
            'grade_subject_id.required' => 'The grade subject ID field is required.',
            'grade_subject_id.integer'  => 'The grade subject ID must be an integer.',
            'grade_subject_id.exists'   => 'The selected grade subject does not exist.',
            'enrollment_id.required'    => 'The enrollment ID field is required.',
            'enrollment_id.integer'     => 'The enrollment ID must be an integer.',
            'enrollment_id.exists'      => 'The selected student enrollment does not exist.',
            'rating.required'           => 'The rating field is required.',
            'rating.string'             => 'The rating must be a string.',
            'rating.in'                 => 'The selected rating is invalid (must be excellent, very_good, good, average, or weak).',
            'notes.string'              => 'The notes must be a string.',
            'notes.max'                 => 'The notes must not exceed 500 characters.',
        ];
    }
}
