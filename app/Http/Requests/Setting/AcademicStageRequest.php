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
}
