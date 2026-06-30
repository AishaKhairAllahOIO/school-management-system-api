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
       return $this->user()->can('student:read'); 
    }

    public function rules(): array
    {
        return [
            'search'         => ['nullable', 'string', 'max:100'],
            'grade_level_id' => ['nullable', 'integer', 'exists:grade_levels,id'],
            'class_room_id'  => ['nullable', 'integer', 'exists:class_rooms,id'],
            'sort'           => ['nullable', 'string', 'in:asc,desc,ASC,DESC'],
            'per_page'       => ['nullable', 'integer', 'min:5', 'max:100'],
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
