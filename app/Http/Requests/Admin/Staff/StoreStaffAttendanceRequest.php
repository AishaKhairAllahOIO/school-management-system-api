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
}
