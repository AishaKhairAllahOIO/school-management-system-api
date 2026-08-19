<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AcademicStageType;
use App\Http\Requests\BaseRequest;

class AcademicStageRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(AcademicStageType::class), 'unique:academic_stages,type,'],
        ];
    }
    public function messages(): array
    {
        return [
            'type.required' => 'The academic stage type field is required.',
            'type.unique'   => 'This academic stage type has already been taken.',
        ];
    }
}
