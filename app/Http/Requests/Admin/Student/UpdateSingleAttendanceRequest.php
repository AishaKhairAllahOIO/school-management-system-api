<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSingleAttendanceRequest extends FormRequest
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
            'semester_id'=>'sometimes|integer|exists:semesters,id',
            'enrollment_id'=>'sometimes|integer|exists:enrollments,id',
            'attendance_date'=>'sometimes|date',
            'status'=>'sometimes|in:present,absent',
            'absence_type'=>'sometimes|string|max:255',
            'class_room_id'=>'sometimes|integer|exists:class_rooms,id',
        ];
    }
}
