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
    public function messages(): array
    {
        return [
            'title.string'                    => 'The exam schedule title must be a string.',
            'title.max'                       => 'The exam schedule title must not exceed 255 characters.',
            
            'type.string'                     => 'The exam schedule type must be a string.',
            'type.in'                         => 'The selected type is invalid (must be either exam or quiz).',
            
            'grade_level_id.exists'           => 'The selected grade level does not exist.',
            
            'subjects.array'                  => 'The subjects must be provided as an array.',
            'subjects.min'                    => 'The exam schedule must contain at least one subject.',
            
            'subjects.*.grade_subject_id.integer' => 'Each grade subject ID must be an integer.',
            'subjects.*.grade_subject_id.exists'  => 'One or more selected grade subjects do not exist.',
            
            'subjects.*.exam_date.date'       => 'Each exam date must be a valid date.',
            'subjects.*.exam_date.after_or_equal' => 'The exam date must be today or a future date.',
            
            'subjects.*.start_time.date_format' => 'Each start time must match the format HH:MM.',
            
            'subjects.*.end_time.date_format' => 'Each end time must match the format HH:MM.',
            'subjects.*.end_time.after'       => 'The exam end time must be after the start time.',
            
            'subjects.*.syllabus.string'      => 'The syllabus must be a string.',
            'subjects.*.syllabus.max'         => 'The syllabus must not exceed 1000 characters.',
            
            'subjects.*.teacher_ids.array'    => 'The teacher IDs must be provided as an array.',
            'subjects.*.teacher_ids.*.integer' => 'Each teacher ID must be an integer.',
            'subjects.*.teacher_ids.*.exists' => 'One or more selected teachers do not exist.',
        ];
    }
}