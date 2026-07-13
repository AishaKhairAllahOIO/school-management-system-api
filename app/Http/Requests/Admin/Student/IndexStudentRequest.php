<?php

namespace App\Http\Requests\Admin\Student;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexStudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
       return true; 
    }

    public function rules(): array
    {
        return [
            'search'         => ['nullable', 'string', 'max:100'],
            'level' => ['nullable', 'integer'],
            'classroom_name'  => ['nullable', 'string', ],
            'sort'           => ['nullable', 'string', 'in:asc,desc,ASC,DESC'],
            'status'         =>['nullable','string'],
        ];
    }

    public function messages(): array
    {
        return [
            'grade_level_id.exists' => 'معرّف الصف الدراسي الممرر غير موجود في المدرسة.',
            'class_room_id.exists'  => 'معرّف الشعبة الممرر غير صحيح.',
            'sort.in'               => 'قيمة الترتيب يجب أن تكون asc أو desc فقط.',
            'per_page.max'          => 'الحد الأقصى المسموح به هو 100 سجل في الصفحة.',
        ];
    }
}
