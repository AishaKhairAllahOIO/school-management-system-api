<?php

namespace App\Http\Requests\Scheduale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\ApiResource;
use App\Enums\SchoolDay;
use Illuminate\Validation\Rule;

class StoreScheduleEntryRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'schedule_id' => ['required', 'exists:schedules,id'],
            'class_room_id' => ['required', 'exists:class_rooms,id'],
            'teacher_id' => ['required', 'exists:staff,id'],
            'teacher_assignment_id' => ['required', 'integer', 'exists:teacher_assignments,id'],
            'grade_subject_id' => ['required', 'exists:grade_subjects,id'],
            'day' => ['required', 'string', Rule::enum(SchoolDay::class)],
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
            'schedule_id.required'           => 'The schedule ID field is required.',
            'schedule_id.exists'             => 'The selected schedule does not exist.',
            
            'class_room_id.required'         => 'The classroom ID field is required.',
            'class_room_id.exists'           => 'The selected classroom does not exist.',
            
            'teacher_id.required'            => 'The teacher ID field is required.',
            'teacher_id.exists'              => 'The selected teacher does not exist.',
            
            'teacher_assignment_id.required' => 'The teacher assignment ID field is required.',
            'teacher_assignment_id.integer'  => 'The teacher assignment ID must be an integer.',
            'teacher_assignment_id.exists'   => 'The selected teacher assignment does not exist.',
            
            'grade_subject_id.required'      => 'The grade subject ID field is required.',
            'grade_subject_id.exists'        => 'The selected grade subject does not exist.',
            
            'day.required'                   => 'The day field is required.',
            'day.string'                     => 'The day must be a valid string.',
        ];
    }
}