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
}
