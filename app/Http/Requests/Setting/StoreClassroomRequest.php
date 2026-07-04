<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreClassroomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return  $this->user()->can('school:initialize');;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'academicYearId' => ['required', 'exists:academic_years,id'],
            'grade_level_id'        => ['required', 'exists:grade_levels,id'],
            'capacity'       => ['required', 'integer', 'min:5', 'max:100'],
        ];
    }
}
