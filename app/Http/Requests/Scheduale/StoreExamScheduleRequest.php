<?php

namespace App\Http\Requests\Scheduale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\ApiResource;

class StoreExamScheduleRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'title'                      => ['required', 'string', 'max:255'],
            'type'                       => ['required', 'string', 'in:exam,quiz'],
            'grade_level_id'             => ['required', 'exists:grade_levels,id'],
            
            'subjects'                   => ['required', 'array', 'min:1'],
            'subjects.*.grade_subject_id'=> ['required', 'exists:grade_subjects,id'],
            'subjects.*.exam_date'       => ['required', 'date', 'after_or_equal:today'],
            'subjects.*.start_time'      => ['required', 'date_format:H:i'],
            'subjects.*.end_time'        => ['required', 'date_format:H:i', 'after:subjects.*.start_time'],
            'subjects.*.syllabus'        => ['nullable', 'string', 'max:1000'],
            
            'subjects.*.teacher_ids'     => ['required', 'array', 'min:1'],
            'subjects.*.teacher_ids.*'   => ['exists:staff,id'],
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
            'title.required'                  => 'The exam schedule title field is required.',
            'title.string'                    => 'The exam schedule title must be a string.',
            'title.max'                       => 'The exam schedule title must not exceed 255 characters.',
            
            'type.required'                   => 'The exam schedule type field is required.',
            'type.string'                     => 'The exam schedule type must be a string.',
            'type.in'                         => 'The selected type is invalid (must be either exam or quiz).',
            
            'grade_level_id.required'         => 'The grade level ID field is required.',
            'grade_level_id.exists'           => 'The selected grade level does not exist.',
            
            'subjects.required'               => 'At least one subject is required for the exam schedule.',
            'subjects.array'                  => 'The subjects must be provided as an array.',
            'subjects.min'                    => 'The exam schedule must contain at least one subject.',
            
            'subjects.*.grade_subject_id.required' => 'Each subject must be associated with a grade subject.',
            'subjects.*.grade_subject_id.exists'  => 'One or more selected grade subjects do not exist.',
            
            'subjects.*.exam_date.required'   => 'Each exam must have a scheduled date.',
            'subjects.*.exam_date.date'       => 'The exam date must be a valid date.',
            'subjects.*.exam_date.after_or_equal' => 'The exam date must be today or a future date.',
            
            'subjects.*.start_time.required'  => 'Each exam must have a start time.',
            'subjects.*.start_time.date_format' => 'The start time must match the format HH:MM.',
            
            'subjects.*.end_time.required'    => 'Each exam must have an end time.',
            'subjects.*.end_time.date_format' => 'The end time must match the format HH:MM.',
            'subjects.*.end_time.after'       => 'The exam end time must be after the start time.',
            
            'subjects.*.syllabus.string'      => 'The syllabus must be a string.',
            'subjects.*.syllabus.max'         => 'The syllabus must not exceed 1000 characters.',
            
            'subjects.*.teacher_ids.required' => 'Each exam subject must be assigned to at least one teacher.',
            'subjects.*.teacher_ids.array'    => 'The teacher IDs must be provided as an array.',
            'subjects.*.teacher_ids.min'      => 'At least one teacher must be selected.',
            'subjects.*.teacher_ids.*.exists' => 'One or more selected teachers do not exist.',
        ];
    }
}