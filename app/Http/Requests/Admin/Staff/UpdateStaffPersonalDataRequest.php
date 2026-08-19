<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use App\Models\Staff;

class UpdateStaffPersonalDataRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $staffId = $this->route('staff');

        $staff = Staff::find($staffId);
        $userId = $staff ? $staff->user_id : null;

        return [
            'first_name'   => ['sometimes', 'string', 'max:50'],
            'last_name'    => ['sometimes', 'string', 'max:50'],
            'father_name'  => ['sometimes', 'string', 'max:50'],
            'mother_name'  => ['sometimes', 'string', 'max:50'],
            'birth_date'   => ['sometimes', 'date'],
            'birth_place'  => ['sometimes', 'string', 'max:100'],
            'address'      => ['sometimes', 'string', 'max:255'],
            'gender'       => ['sometimes', 'in:male,female'],
            'nationality'  => ['sometimes', 'in:syrian,lebanese,palestinian,jordanian,other'],
            'photo_url'    => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'degree'           => ['sometimes','nullable', 'in:diploma,bachelor,master,phd,other'],
            'specialization'   => ['sometimes','nullable', 'string'],
            'university'       => ['sometimes','nullable', 'string'],
            'graduation_year'  => ['sometimes','nullable', 'integer'],
            'hire_date'        => ['sometimes','nullable', 'date'],
            'experience_years' => ['sometimes','nullable', 'integer', 'min:0'],
            'service_type'     => ['sometimes','nullable','string'],

            'phone_number' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($userId)
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'first_name.string'     => 'The first name must be a string.',
            'first_name.max'        => 'The first name must not exceed 50 characters.',
            'last_name.string'      => 'The last name must be a string.',
            'last_name.max'         => 'The last name must not exceed 50 characters.',
            'father_name.string'    => 'The father name must be a string.',
            'father_name.max'       => 'The father name must not exceed 50 characters.',
            'mother_name.string'    => 'The mother name must be a string.',
            'mother_name.max'       => 'The mother name must not exceed 50 characters.',
            'birth_date.date'       => 'The birth date must be a valid date.',
            'birth_place.string'    => 'The birth place must be a string.',
            'birth_place.max'       => 'The birth place must not exceed 100 characters.',
            'address.string'        => 'The address must be a string.',
            'address.max'           => 'The address must not exceed 255 characters.',
            'gender.in'             => 'The selected gender is invalid.',
            'nationality.in'        => 'The selected nationality is invalid.',
            'photo_url.image'       => 'The file must be an image.',
            'photo_url.mimes'       => 'The image must be a file of type: jpeg, png, jpg, webp.',
            'degree.in'             => 'The selected degree is invalid.',
            'specialization.string' => 'The specialization must be a string.',
            'university.string'     => 'The university must be a string.',
            'graduation_year.integer' => 'The graduation year must be an integer.',
            'hire_date.date'        => 'The hire date must be a valid date.',
            'experience_years.integer' => 'The experience years must be an integer.',
            'experience_years.min'  => 'The experience years cannot be negative.',
            'phone_number.string'   => 'The phone number must be a string.',
            'phone_number.max'      => 'The phone number must not exceed 20 characters.',
            'phone_number.unique'   => 'The phone number has already been taken.',
            'email.email'           => 'The email must be a valid email address.',
            'email.unique'          => 'The email has already been taken.',
        ];
    }
    
}
