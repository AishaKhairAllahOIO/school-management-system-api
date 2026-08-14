<?php

namespace App\Http\Requests\Scheduale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\ApiResource;

class UpdateExamScheduleRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title'                      => ['sometimes', 'required', 'string', 'max:255'],
            'type'                       => ['sometimes', 'required', 'string', 'in:exam,quiz'],
            'grade_level_id'             => ['sometimes', 'required', 'exists:grade_levels,id'],
            
            'subjects'                   => ['sometimes', 'required', 'array', 'min:1'],
            
            'subjects.*.grade_subject_id'=> ['required', 'integer', 'exists:grade_subjects,id'],
            'subjects.*.exam_date'       => ['required', 'date', 'after_or_equal:today'],
            'subjects.*.start_time'      => ['required', 'date_format:H:i'],
            'subjects.*.end_time'        => ['required', 'date_format:H:i', 'after:subjects.*.start_time'],
            'subjects.*.syllabus'        => ['nullable', 'string', 'max:1000'],
            
            'subjects.*.teacher_ids'     => ['nullable', 'array'],
            'subjects.*.teacher_ids.*'   => ['integer', 'exists:staff,id'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse('Validation errors occurred', 422, $validator->errors())
        );
    }
}