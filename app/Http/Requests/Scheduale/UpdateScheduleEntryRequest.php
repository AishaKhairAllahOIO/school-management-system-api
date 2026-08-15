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
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            $this->errorResponse('Validation errors occurred', 422, $validator->errors())
        );
    }
}