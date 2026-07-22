<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Models\Guardian;
use Illuminate\Validation\Rule;


class UpdateGuardianPersonalDataRequest extends FormRequest
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
       $parentId = $this->route('guardian'); 
        
        // 2. جلب سجل ولي الأمر لمعرفة الـ user_id الخاص به
        $parentRecord = Guardian::find($parentId); // استبدلي Guardian باسم الموديل الصحيح لديكم
        $userId = $parentRecord ? $parentRecord->user_id : null;

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
            
            // 🔥 تجاهل رقم الهاتف لولي الأمر هذا تحديداً
            'phone_number' => [
                'sometimes', 
                'string', 
                'max:20', 
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],
            
            // 🔥 تجاهل الإيميل لولي الأمر هذا تحديداً
            'email' => [
                'nullable', 
                'email', 
                Rule::unique('users', 'email')->ignore($userId)
            ],
        ];
    }
}
