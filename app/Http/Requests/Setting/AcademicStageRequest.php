<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\AcademicStageType;
use App\Http\Requests\BaseRequest;

class AcademicStageRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
   public function authorize(): bool { return $this->user()->can('school:initialize'); }
    public function rules(): array {
        return [
            'type' => ['required', new Enum(AcademicStageType::class), 'unique:academic_stages,type,' . $this->route('stage')],
        ];
    }
}
