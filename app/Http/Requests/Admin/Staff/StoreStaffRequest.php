<?php

namespace App\Http\Requests\Admin\Staff;

use App\Http\Requests\BaseRequest;

class StoreStaffRequest extends BaseRequest
{
    public function authorize(): bool
    {
       return true;
    }

    public function rules(): array
    {
        return [
            'phone_number'   => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'first_name'     => ['required', 'string', 'max:50'],
            'last_name'      => ['required', 'string', 'max:50'],
            'father_name'    => ['required', 'string', 'max:50'],
            'mother_name'    => ['required', 'string', 'max:50'],
            'birth_date'     => ['required', 'date'],
            'birth_place'    => ['required', 'string', 'max:100'],
            'address'        => ['required', 'string', 'max:255'],
            'gender'         => ['required', 'in:male,female'],
            'nationality'    => ['nullable', 'in:syrian,lebanese,palestinian,jordanian,other'],
            'photo_url'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'email'          => ['required_unless:role,service_staff', 'nullable', 'email', 'unique:users,email'],

            'degree'           => ['nullable', 'string', 'in:diploma,bachelor,master,phd,student,none,other'],
            'specialization'   => ['nullable', 'string', 'max:100'],
            'university'       => ['nullable', 'string', 'max:255'],
            'graduation_year'  => ['nullable', 'integer', 'min:1950', 'max:' . (date('Y') + 5)],

            'hire_date'        => ['required', 'date'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:50'],

            'role'             => ['required', 'string', 'in:teacher,adviser,counselor,secretary,service_staff,super_admin'],
            'password'         => [
                'required_if:role,secretary,adviser,super_admin',
                'nullable',
                'string',
                'min:6'
            ],
         'service_type'     => [ 'nullable', 'string', 'in:cleaner,other,security,maintenance,kitchen_staff'],

        ];
    }

   public function messages(): array
    {
        return [
            'phone_number.required'     => 'The phone number is required.',
            'phone_number.unique'       => 'This phone number is already taken by another user.',
            'first_name.required'       => 'The first name is required.',
            'last_name.required'        => 'The last name is required.',
            'father_name.required'      => 'The father name is required.',
            'mother_name.required'      => 'The mother name is required.',
            'birth_date.required'       => 'The birth date is required.',
            'birth_date.date'           => 'The birth date must be a valid date.',
            'birth_place.required'      => 'The birth place is required.',
            'address.required'          => 'The address is required.',
            'gender.required'           => 'The gender is required.',
            'gender.in'                 => 'The gender must be either male or female.',
            'nationality.in'            => 'The selected nationality is invalid.',
            'photo_url.image'           => 'The photo must be an image file.',
            'photo_url.mimes'           => 'The photo must be of type: jpeg, png, jpg, webp.',
            'email.required_unless'     => 'The email field is required unless the role is service staff.',
            'email.email'               => 'The email must be a valid email address.',
            'email.unique'              => 'This email address is already taken.',
            'degree.in'                 => 'The selected degree is invalid.',
            'graduation_year.integer'   => 'The graduation year must be an integer.',
            'graduation_year.min'       => 'The graduation year cannot be earlier than 1950.',
            'graduation_year.max'       => 'The graduation year is invalid.',
            'hire_date.required'        => 'The hire date is required.',
            'hire_date.date'            => 'The hire date must be a valid date.',
            'experience_years.integer'  => 'The experience years must be an integer.',
            'experience_years.min'      => 'The experience years cannot be less than 0.',
            'experience_years.max'      => 'The experience years cannot exceed 50.',
            'role.required'             => 'The user role is required.',
            'role.in'                   => 'The selected role is invalid. Allowed roles are: teacher, adviser, counselor, secretary, service_staff, super_admin.',
            'password.required_if'      => 'The password field is required for selected role (super_admin, adviser, secretary).',
            'password.min'              => 'The password must be at least 6 characters.',
            'service_type.in'           => 'The selected service type is invalid.',
        ];
    }
}
