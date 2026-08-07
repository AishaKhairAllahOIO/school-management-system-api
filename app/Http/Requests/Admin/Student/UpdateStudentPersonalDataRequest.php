<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Student;


class UpdateStudentPersonalDataRequest extends FormRequest
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
        $studentId = $this->route('student');

        $student = Student::with('guardian')->find($studentId);

        $userId = $student ? $student->user_id : null;
        $guardianUserId = ($student && $student->guardian) ? $student->guardian->user_id : null;

        return [
            'first_name' => ['sometimes', 'string', 'max:50'],
            'last_name' => ['sometimes', 'string', 'max:50'],
            'father_name' => ['sometimes', 'string', 'max:50'],
            'mother_name' => ['sometimes', 'string', 'max:50'],
            'birth_date' => ['sometimes', 'date'],
            'birth_place' => ['sometimes', 'string', 'max:100'],
            'address' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'in:male,female'],
            'nationality' => ['sometimes', 'in:syrian,lebanese,palestinian,jordanian,other'],
            'photo_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],

            'guardian_first_name' => ['sometimes', 'string', 'max:50'],
            'guardian_last_name' => ['sometimes', 'string', 'max:50'],
            'guardian_father_name' => ['sometimes', 'string', 'max:50'],
            'guardian_mother_name' => ['sometimes', 'string', 'max:50'],
            'guardian_birth_date' => ['sometimes', 'date'],
            'guardian_birth_place' => ['sometimes', 'string', 'max:100'],
            'guardian_address' => ['sometimes', 'string', 'max:255'],
            'guardian_gender' => ['sometimes', 'in:male,female'],
            'guardian_nationality' => ['sometimes', 'in:syrian,lebanese,palestinian,jordanian,other'],
            'guardian_national_id' => ['sometimes', 'string', 'max:50'],
            'guardian_photo_url' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'guardian_phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($guardianUserId)
            ],
            'guardian_email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($guardianUserId)
            ],

            'class_room_id' => ['sometimes', 'exists:class_rooms,id'],
            'grade_level_id' => ['sometimes', 'exists:grade_levels,id'],
            'academic_year_id' => ['sometimes', 'exists:academic_years,id'],
            //'enrollment_status' => ['sometimes', 'in:suspended,enrolled,completed'],
        ];
    }
}
