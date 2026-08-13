<?php

namespace App\Http\Requests\Scheduale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\ApiResource; 

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
            'day'              => ['required', 'string', 'in:sunday,monday,tuesday,wednesday,thursday'],
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