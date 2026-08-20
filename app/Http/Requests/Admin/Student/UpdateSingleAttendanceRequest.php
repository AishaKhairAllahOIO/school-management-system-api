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
    public function messages(): array
    {
        return [
            'semester_id.integer'     => 'The semester ID must be an integer.',
            'semester_id.exists'      => 'The selected semester does not exist.',
            'enrollment_id.integer'   => 'The enrollment ID must be an integer.',
            'enrollment_id.exists'    => 'The selected enrollment does not exist.',
            'attendance_date.date'    => 'The attendance date must be a valid date.',
            'status.in'               => 'The status must be either present or absent.',
            'absence_type.string'     => 'The absence type must be a string.',
            'absence_type.max'        => 'The absence type must not exceed 255 characters.',
            'class_room_id.integer'   => 'The classroom ID must be an integer.',
            'class_room_id.exists'    => 'The selected classroom does not exist.',
        ];
    }
}
