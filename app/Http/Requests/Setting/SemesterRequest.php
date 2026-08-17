<?php

namespace App\Http\Requests\Setting;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequest;

class SemesterRequest extends BaseRequest
{

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $rules = [
            'academicYearId' => 'required|exists:academic_years,id',
            'semesterName' => 'required|string|max:255',
            'startDate' => 'required|date',
            'endDate' => 'required|date|after:startDate',
            'isCurrent' => 'required|boolean',
            'isFinalTerm' => 'required|boolean',
        ];
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules = array_map(fn($rule) => 'sometimes|' . $rule, $rules);
        }
        return $rules;
    }
}
