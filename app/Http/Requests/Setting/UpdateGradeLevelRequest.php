<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\GradeName;
use Illuminate\Validation\Rules\Enum;

class UpdateGradeLevelRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {

$routeParam = $this->route('grade') ;
        
        // 2. استخراج الـ ID سواء كان لارافيل يمرره كـ Model Object أو كرقم مباشر
        $gradeId = is_object($routeParam) ? $routeParam->id : $routeParam;
        return [
            'academicStageId' => ['sometimes', 'required', 'exists:academic_stages,id'],
            'name' => [
                    'sometimes', 
                    new Enum(GradeName::class), 
                    Rule::unique('grade_levels', 'name')->ignore($gradeId)
                ],            
            'isGraduationGrade' => ['sometimes', 'boolean'],

        ];

    }
    public function messages(): array
    {
        return [
            'academicStageId.required' => 'The academic stage ID field is required when present.',
            'academicStageId.exists'   => 'The selected academic stage does not exist.',
            
            'name.unique'              => 'This grade level name has already been taken.',
            
            'isGraduationGrade.boolean'=> 'The graduation grade flag must be true or false.',
        ];
    }
}
