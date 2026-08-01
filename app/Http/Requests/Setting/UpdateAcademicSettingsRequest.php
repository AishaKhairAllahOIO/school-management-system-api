<?php

namespace App\Http\Requests\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\BaseRequest;
use App\Enums\SchoolDay;
use Illuminate\Validation\Rules\Enum;



class UpdateAcademicSettingsRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('school:initialize');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
   public function rules(): array
    {
        return [
            'currentAcademicYearId'       => 'required|exists:academic_years,id',
            'currentSemesterId'           => 'required|exists:semesters,id',
            'scheduleSettings'                       => 'required|array',
            'scheduleSettings.dayStartTime'          => 'required|date_format:H:i',
            'scheduleSettings.periodDurationMinutes' => 'required|integer|min:20|max:120',

            'scheduleSettings.workingDays'                => 'required|array|min:1',
            'scheduleSettings.workingDays.*.day'          => ['required', new Enum(SchoolDay::class)],
            'scheduleSettings.workingDays.*.periodsCount' => 'required|integer|min:1|max:15',

            'scheduleSettings.breaks'                    => 'present|array',
            'scheduleSettings.breaks.*.id'               => 'required|string',
            'scheduleSettings.breaks.*.afterPeriodIndex' => 'required|integer|min:1',
            'scheduleSettings.breaks.*.durationMinutes'  => 'required|integer|min:5',

        ];
    }
    public function messages(){
    return [
];
}

}
