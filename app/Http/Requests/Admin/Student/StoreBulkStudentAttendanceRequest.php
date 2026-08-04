<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBulkStudentAttendanceRequest extends FormRequest
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
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'class_room_id' => ['required', 'integer', 'exists:class_rooms,id'],
            'attendance_date' => ['required', 'date', 'before_or_equal:today'],
            
            'attendances' => ['required', 'array', 'min:1'],
            'attendances.*.enrollment_id' => ['required', 'integer', 'exists:enrollments,id'],
            'attendances.*.status' => ['required', 'in:present,absent,late,excused'], 
            // تم تصحيح الحقل ليتطابق مع جدولك
            'attendances.*.absence_type' => ['nullable', 'string', 'max:255'], 
        ];
    }
    public function messages(): array
    {
        return [
            'semester_id.required' => 'The semester field is required.',
            'semester_id.integer' => 'The semester must be an integer.',
            'semester_id.exists' => 'The selected semester is invalid.',
            'class_room_id.required' => 'The class room field is required.',
            'class_room_id.integer' => 'The class room must be an integer.',
            'class_room_id.exists' => 'The selected class room is invalid.',
            'attendance_date.required' => 'The attendance date field is required.',
            'attendance_date.date' => 'The attendance date is not a valid date.',
            'attendance_date.before_or_equal' => 'The attendance date cannot be in the future.',
            'attendances.required' => 'At least one attendance record is required.',
            'attendances.array' => 'The attendances must be an array.',
            'attendances.min' => 'At least one attendance record is required.',
            'attendances.*.enrollment_id.required' => 'Each attendance record must have an enrollment ID.',
            'attendances.*.enrollment_id.integer' => 'Each enrollment ID must be an integer.',
            'attendances.*.enrollment_id.exists' => 'One or more enrollment IDs are invalid.',
            'attendances.*.status.required' => 'Each attendance record must have a status.',
            'attendances.*.status.in' => "Each status must be one of the following: present, absent, late, excused.",
        ];
    }
}
