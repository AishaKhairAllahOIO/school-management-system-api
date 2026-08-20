<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Student;

class UpdateStudentRequest extends FormRequest
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
       $studentId = $this->route('id');

        $student = Student::find($studentId);
        $userId  = $student ? $student->user_id : null;

        return [
            'user'              => ['sometimes', 'array'],
            'user.first_name'   => ['sometimes', 'string', 'max:50'],
            'user.last_name'    => ['sometimes', 'string', 'max:50'],
            'user.father_name'  => ['sometimes', 'string', 'max:50'],
            'user.mother_name'  => ['sometimes', 'string', 'max:50'],
            'user.birth_date'   => ['sometimes', 'date'],
            'user.birth_place'  => ['sometimes', 'string', 'max:100'],
            'user.address'      => ['sometimes', 'string', 'max:255'],
            'user.gender'       => ['sometimes', 'in:male,female'],
            'user.nationality'  => ['sometimes', 'string', 'max:50'],

            'user.phone_number' => [
                'sometimes',
                'string',
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],


        ];
    }

 public function messages(): array
    {
        return [
            'user.first_name.string'   => 'The first name must be a string.',
            'user.first_name.max'      => 'The first name must not exceed 50 characters.',
            'user.last_name.string'    => 'The last name must be a string.',
            'user.last_name.max'       => 'The last name must not exceed 50 characters.',
            'user.father_name.string'  => 'The father name must be a string.',
            'user.father_name.max'     => 'The father name must not exceed 50 characters.',
            'user.mother_name.string'  => 'The mother name must be a string.',
            'user.mother_name.max'     => 'The mother name must not exceed 50 characters.',
            'user.birth_date.date'     => 'The birth date must be a valid date.',
            'user.birth_place.string'  => 'The birth place must be a string.',
            'user.birth_place.max'     => 'The birth place must not exceed 100 characters.',
            'user.address.string'      => 'The address must be a string.',
            'user.address.max'         => 'The address must not exceed 255 characters.',
            'user.gender.in'           => 'The selected gender is invalid.',
            'user.nationality.string'  => 'The nationality must be a string.',
            'user.nationality.max'     => 'The nationality must not exceed 50 characters.',
            'user.phone_number.string' => 'The phone number must be a string.',
            'user.phone_number.unique' => 'The phone number has already been taken.',
        ];
    }
    
}
