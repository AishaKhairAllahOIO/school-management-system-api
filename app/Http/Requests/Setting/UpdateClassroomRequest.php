<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassroomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'capacity' => ['sometimes', 'required', 'integer', 'min:5', 'max:100'],
            'grade_level_id' => ['sometimes', 'required', 'exists:grade_levels,id'],
        ];
    }
}
