<?php

namespace App\Http\Requests\Admin\Staff;

use App\Http\Requests\BaseRequest;

class StoreStaffRequest extends BaseRequest
{
    public function authorize(): bool
    {
       // return $this->user()->can('staff:create'); // أو الصلاحية المناسبة
       return true;
    }

    public function rules(): array
    {
        return [
            // ----- 👤 البيانات الشخصية (جدول users) -----
            'phone_number'   => ['required', 'string', 'max:20', 'unique:users,phone_number'],
            'first_name'     => ['required', 'string', 'max:50'],
            'last_name'      => ['required', 'string', 'max:50'],
            'father_name'    => ['required', 'string', 'max:50'],
            'mother_name'    => ['required', 'string', 'max:50'],
            'birth_date'     => ['required', 'date'], // يجب أن يكون بالغاً
            'birth_place'    => ['required', 'string', 'max:100'],
            'address'        => ['required', 'string', 'max:255'],
            'gender'         => ['required', 'in:male,female'],
            'nationality'    => ['nullable', 'in:syrian,lebanese,palestinian,jordanian,other'],
            'photo_url'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp'],
            'email'          =>['required', 'email','unique:users,email'],

            // ----- 💼 البيانات الوظيفية والأكاديمية (جدول staff) -----
            'degree'           => ['required', 'in:diploma,bachelor,master,phd,other'],
            'specialization'   => ['required', 'string', 'max:100'],
            'university'       => ['required', 'string', 'max:255'],
            'graduation_year'  => ['required', 'integer', 'min:1950', 'max:' . date('Y')],
            'hire_date'        => ['required', 'date'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:50'],
        ];
    }
}