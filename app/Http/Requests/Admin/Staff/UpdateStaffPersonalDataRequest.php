<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // 🔥 يجب استدعاء هذا الكلاس

use App\Models\Staff;

class UpdateStaffPersonalDataRequest extends FormRequest
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
        $staffId = $this->route('staff'); 
        
        // 2. جلب سجل الموظف لمعرفة الـ user_id الخاص به في جدول users
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
            'degree'           => ['sometimes', 'in:diploma,bachelor,master,phd,other'],
            'specialization'   => ['sometimes', 'string'],
            'university'       => ['sometimes', 'string'],
            'graduation_year'  => ['sometimes', 'integer'],
            'hire_date'        => ['sometimes', 'date'],
            'experience_years' => ['sometimes', 'integer', 'min:0'],
            'service_type'     => ['sometimes','string'], 
            
            // 🔥 تجاهل رقم الهاتف لهذا المستخدم تحديداً
            'phone_number' => [
                'sometimes', 
                'string', 
                'max:20', 
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],
            
            // 🔥 تجاهل الإيميل لهذا المستخدم تحديداً
            'email' => [
                'nullable', 
                'email', 
                Rule::unique('users', 'email')->ignore($userId)
            ],
        ];
    }
}
