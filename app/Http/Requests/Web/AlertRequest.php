<?php

namespace App\Http\Requests\Web;

use App\Models\Alert;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AlertRequest extends FormRequest
{
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
        $rules = [
            'audience' => ['required', Rule::in([Alert::AUDIENCE_STUDENT, Alert::AUDIENCE_STAFF])],
            'type' => [
                'required',
                Rule::in([
                    Alert::TYPE_ABSENCE,
                    Alert::TYPE_BEHAVIOR,
                    Alert::TYPE_LATE,
                    Alert::TYPE_ESCAPE,
                    Alert::TYPE_PAYMENT,
                    Alert::TYPE_SALARY,
                    Alert::TYPE_HOMEWORK,
                    Alert::TYPE_PAYED,
                    Alert::TYPE_EXPULSION,
                    Alert::TYPE_WARNING
                ])
            ],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->input('audience') === Alert::AUDIENCE_STUDENT) {
            $rules['enrollment_ids'] = ['required', 'array', 'min:1'];
            $rules['enrollment_ids.*'] = ['integer', 'exists:enrollments,id'];
        } else {
            $rules['staff_ids'] = ['required', 'array', 'min:1'];
            $rules['staff_ids.*'] = ['integer', 'exists:staff,id'];
        }

        $rules += match ($this->input('type')) {
            Alert::TYPE_PAYMENT => [
                'meta.amount_due' => ['nullable', 'numeric', 'min:0'],
                'meta.due_date' => ['nullable', 'date'],
            ],
            Alert::TYPE_LATE => [
                'meta.minutes_late' => ['nullable', 'integer', 'min:1'],
                'meta.session' => ['nullable', 'string', 'max:255'],
            ],
            Alert::TYPE_BEHAVIOR => [
                'meta.severity' => ['nullable', Rule::in(['low', 'medium', 'high'])],
            ],
            Alert::TYPE_ESCAPE => [
                'meta.session' => ['nullable', 'string', 'max:255'],
            ],
            Alert::TYPE_ABSENCE => [
                'meta.date' => ['nullable', 'date'],
            ],
            Alert::TYPE_SALARY => [
                'meta.amount' => ['nullable', 'numeric'],
                'meta.month' => ['nullable', 'date'],
            ],
            Alert::TYPE_HOMEWORK => [
                'meta.subject' => ['required', 'string'],
                'meta.date' => ['nullable', 'date'],
            ],
            Alert::TYPE_PAYED => [
                'meta.amount' => ['nullable', 'numeric'],
            ],
            Alert::TYPE_EXPULSION => [
                'meta.law_id' => ['required', 'integer', 'exists:school_laws,id']
            ],
            Alert::TYPE_WARNING => [
                'meta.reason' => ['nullable', 'string', 'max:255'],
                'meta.absence_count' => ['nullable', 'integer'] 
            ],

            default => [],
        };

        return $rules;
    }
}
