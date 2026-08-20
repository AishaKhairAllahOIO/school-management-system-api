<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PreviewPayrollRequest extends FormRequest
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
            'staff_id'       => ['required', 'integer', 'exists:staff,id'],
            'year'           => ['required', 'integer', 'min:2020', 'max:2099'],
            'month'          => ['required', 'integer', 'min:1', 'max:12'],
        ];
    }
    public function messages(): array
    {
        return [
            'staff_id.required' => 'The staff ID is required.',
            'staff_id.integer'  => 'The staff ID must be an integer.',
            'staff_id.exists'   => 'The selected staff member does not exist in the system.',

            'year.required'     => 'The year is required.',
            'year.integer'      => 'The year must be a valid integer.',
            'year.min'          => 'The year cannot be earlier than 2020.',
            'year.max'          => 'The year cannot exceed 2099.',

            'month.required'    => 'The month is required.',
            'month.integer'     => 'The month must be a valid integer.',
            'month.min'         => 'The month must be between 1 and 12.',
            'month.max'         => 'The month must be between 1 and 12.',
        ];
    }
}
