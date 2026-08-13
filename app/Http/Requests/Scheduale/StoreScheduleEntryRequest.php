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
            'schedule_id'      => ['required', 'exists:schedules,id'],
            'class_room_id'    => ['required', 'exists:classrooms,id'],
            'teacher_id'       => ['required', 'exists:staff,id'],
            'grade_subject_id' => ['required', 'exists:grade_subjects,id'],
            'day'              => ['required', 'string', Rule::in([SchoolDay::class])],
            'period_index'     => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse('Validation errors occurred', 422, $validator->errors())
        );
    }
}