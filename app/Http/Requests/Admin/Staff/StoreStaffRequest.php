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
            'password.required_if' => 'The password field is required for selected role (super_admin,adviser,secretary).',
            'role.in'              => 'The selected role is invalid. Allowed roles are: teacher, adviser, counselor, secretary, service_staff, super_admin.',
        ];
    }
}
