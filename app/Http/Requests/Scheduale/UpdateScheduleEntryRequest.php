<?php

namespace App\Http\Requests\Scheduale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\ApiResource; 

class UpdateScheduleEntryRequest extends FormRequest
{
    use ApiResource;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'teacher_id'       => ['sometimes', 'exists:staff,id'],
            'grade_subject_id' => ['sometimes', 'exists:grade_subjects,id'],
            'day'              => ['sometimes', 'string', 'in:sunday,monday,tuesday,wednesday,thursday'],
            'period_index'     => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'day.in' => 'The day must be a valid school day (sunday to thursday).',
        ];
    }


    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse('Validation errors occurred', 422, $validator->errors())
        );
    }
}
