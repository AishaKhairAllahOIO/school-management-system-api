<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpadateStaffLeaveRequest extends FormRequest
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
            'staff_id'         => ['sometimes', 'integer', 'exists:staff,id'],
            'leave_type_id'    => ['sometimes', 'integer', 'exists:staff_leave_types,id'],
            'academic_year_id' => ['sometimes', 'integer', 'exists:academic_years,id'],
            'start_date'       => ['sometimes', 'date'],
            'end_date'         => ['sometimes', 'date', 'after_or_equal:start_date'],
        ];
    }
    public function messages(): array
    {
        return [
            'staff_id.integer'          => 'The staff ID must be an integer.',
            'staff_id.exists'           => 'The selected staff member does not exist.',

            'leave_type_id.integer'     => 'The leave type ID must be an integer.',
            'leave_type_id.exists'      => 'The selected leave type does not exist.',

            'academic_year_id.integer'  => 'The academic year ID must be an integer.',
            'academic_year_id.exists'   => 'The selected academic year does not exist.',

            'start_date.date'           => 'The start date must be a valid date format.',

            'end_date.date'             => 'The end date must be a valid date format.',
            'end_date.after_or_equal'   => 'The end date must be a date after or equal to the start date.',
        ];
    }
}
