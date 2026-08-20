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
    public function messages(): array
    {
        return [
            'first_name.string'           => 'The first name must be a string.',
            'first_name.max'              => 'The first name must not exceed 50 characters.',
            'last_name.string'            => 'The last name must be a string.',
            'last_name.max'               => 'The last name must not exceed 50 characters.',
            'father_name.string'          => 'The father name must be a string.',
            'father_name.max'             => 'The father name must not exceed 50 characters.',
            'mother_name.string'          => 'The mother name must be a string.',
            'mother_name.max'             => 'The mother name must not exceed 50 characters.',
            'birth_date.date'             => 'The birth date must be a valid date.',
            'birth_place.string'          => 'The birth place must be a string.',
            'birth_place.max'             => 'The birth place must not exceed 100 characters.',
            'address.string'              => 'The address must be a string.',
            'address.max'                 => 'The address must not exceed 255 characters.',
            'gender.in'                   => 'The selected gender is invalid.',
            'nationality.in'              => 'The selected nationality is invalid.',
            'photo_url.image'             => 'The file must be an image.',
            'photo_url.mimes'             => 'The image must be a file of type: jpeg, png, jpg, webp.',
            'phone_number.string'         => 'The phone number must be a string.',
            'phone_number.max'            => 'The phone number must not exceed 20 characters.',
            'phone_number.unique'         => 'The phone number has already been taken.',
            
            'guardian_first_name.string'  => 'The guardian first name must be a string.',
            'guardian_last_name.string'   => 'The guardian last name must be a string.',
            'guardian_phone_number.unique'=> 'The guardian phone number has already been taken.',
            'guardian_email.email'        => 'The guardian email must be a valid email address.',
            'guardian_email.unique'       => 'The guardian email has already been taken.',

            'class_room_id.exists'        => 'The selected classroom does not exist.',
            'grade_level_id.exists'       => 'The selected grade level does not exist.',
            'academic_year_id.exists'     => 'The selected academic year does not exist.',
        ];
    }
    
}
