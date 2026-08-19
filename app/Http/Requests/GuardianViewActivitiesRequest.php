<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class GuardianViewActivitiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'student_id.required' => 'The student ID field is required.',
            'student_id.integer'  => 'The student ID must be an integer.',
            'student_id.exists'   => 'The selected student does not exist in the system.',
        ];
    }
}
