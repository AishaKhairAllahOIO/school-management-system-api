<?php

namespace App\Http\Requests\Admin\Staff;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherAssignmentRequest extends FormRequest
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
        return [
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'teacher_id'       => ['required', 'exists:staff,id'],
            
            'grade_subject_id' => ['required', 'exists:grade_subjects,id'], 
            
            'class_room_ids'    => ['required', 'array', 'min:1'],
            'class_room_ids.*'  => ['integer', 'exists:class_rooms,id'], 
        ];
    }
    
    public function messages(): array
    {
        return [
            'classroom_ids.required' => 'يجب تحديد شعبة واحدة على الأقل لتكليف المعلم بها.',
            'grade_subject_id.required' => 'يجب تحديد المادة (المقرر) المراد التكليف بها.',
            'grade_subject_id.exists' => 'المادة المحددة غير موجودة في النظام.',
        ];
    }
}
