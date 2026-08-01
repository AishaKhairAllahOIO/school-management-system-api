<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class AcademicYearRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
    }
    public function rules(): array
    {
        $rules = [
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'isCurrent' => 'required|boolean',
        ];
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(fn($rule) => 'sometimes|' . $rule, $rules);
        }
        return $rules;
    }
}
