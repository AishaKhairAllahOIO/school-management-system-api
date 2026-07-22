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
        
        // 2. جلب سجل الطالب لمعرفة الـ user_id الخاص به
        $student = Student::find($studentId);
        $userId = $student ? $student->user_id : null;

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
            
            // 🔥 تجاهل رقم الهاتف لهذا الطالب تحديداً
            'phone_number' => [
                'sometimes', 
                'string', 
                'max:20', 
                Rule::unique('users', 'phone_number')->ignore($userId)
            ],
            
            // 🔥 تجاهل الإيميل لهذا الطالب تحديداً
            'email' => [
                'nullable', 
                'email', 
                Rule::unique('users', 'email')->ignore($userId)
            ],
        ];
    }
}
