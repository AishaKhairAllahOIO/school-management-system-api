<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffAttendanceRequest extends FormRequest
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
            'status'           => ['nullable', 'string', 'in:present,absent,on_leave,partial_absence'],
            'absence_type'     => ['nullable', 'string', 'in:excused,unexcused'],
            'staff_leave_id'   => ['nullable', 'integer', 'exists:staff_leaves,id'],
            
            'missing_periods'   => ['nullable', 'array', 'required_if:status,partial_absence'],
            'missing_periods.*' => ['integer', 'exists:schedule_entries,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'status.string'             => 'The attendance status must be a valid string.',
            'status.in'                 => 'The attendance status must be one of: present, absent, on_leave, partial_absence.',

            'absence_type.string'       => 'The absence type must be a valid string.',
            'absence_type.in'           => 'The absence type must be either excused or unexcused.',

            'staff_leave_id.integer'    => 'The staff leave ID must be an integer.',
            'staff_leave_id.exists'     => 'The selected staff leave record does not exist.',

            'missing_periods.array'     => 'The missing periods must be formatted as an array.',
            'missing_periods.required_if' => 'Missing periods are required when the status is partial absence.',
            
            'missing_periods.*.integer' => 'Each missing period ID must be an integer.',
            'missing_periods.*.exists'  => 'One or more selected schedule entries do not exist.',
        ];
    }
}
