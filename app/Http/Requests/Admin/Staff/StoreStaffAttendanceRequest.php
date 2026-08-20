<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreStaffAttendanceRequest extends FormRequest
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
            'staff_id'               => ['required', 'integer', 'exists:staff,id'],
            'attendance_date'        => ['required', 'date'],
            'status'                 => ['required', 'string', 'in:present,absent,partial_absence,on_leave'],
            'absence_type'           => ['nullable', 'string', 'in:excused,unexcused'],
            
            // تحقق الحصص للدوام الجزئي
            'missing_periods'        => ['nullable', 'array'],
            'missing_periods.*' => ['required_with:missing_periods', 'integer', 'exists:schedule_entries,id'],
        ];
    }
    public function messages(): array
    {
        return [
            'staff_id.required'          => 'The staff ID is required.',
            'staff_id.integer'           => 'The staff ID must be an integer.',
            'staff_id.exists'            => 'The selected staff member does not exist in the system.',

            'attendance_date.required'   => 'The attendance date is required.',
            'attendance_date.date'       => 'The attendance date must be a valid date format.',

            'status.required'            => 'The attendance status is required.',
            'status.string'              => 'The attendance status must be a valid string.',
            'status.in'                  => 'The attendance status must be one of: present, absent, partial_absence, on_leave.',

            'absence_type.string'        => 'The absence type must be a valid string.',
            'absence_type.in'            => 'The absence type must be either excused or unexcused.',

            'missing_periods.array'      => 'The missing periods must be formatted as an array.',
            'missing_periods.*.required_with' => 'Each missing period ID is required when missing periods are provided.',
            'missing_periods.*.integer'  => 'Each missing period ID must be an integer.',
            'missing_periods.*.exists'   => 'One or more selected schedule entries do not exist.',
        ];
    }
}
