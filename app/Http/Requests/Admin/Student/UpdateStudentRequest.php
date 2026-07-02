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
        return $this->user()->can('student:edit');
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
            // مصفوفة الشخص الطبيعي (جدول users)
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
            'user.phone_number.unique' => 'رقم الهاتف الممرر مسجل لمستخدم آخر في المدرسة.',
            'guardian_id.exists'       => 'معرف ولي الأمر الممرر غير موجود في النظام.',
        ];
    }
}
