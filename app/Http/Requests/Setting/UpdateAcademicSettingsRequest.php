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
public function messages(): array
    {
        return [
            'currentAcademicYearId.required'        => 'The current academic year ID field is required.',
            'currentAcademicYearId.exists'          => 'The selected academic year does not exist.',
            
            'currentSemesterId.required'            => 'The current semester ID field is required.',
            'currentSemesterId.exists'              => 'The selected semester does not exist.',
            
            'scheduleSettings.required'             => 'The schedule settings field is required.',
            'scheduleSettings.array'                 => 'The schedule settings must be an array.',
            
            'scheduleSettings.dayStartTime.required' => 'The day start time is required.',
            'scheduleSettings.dayStartTime.date_format' => 'The day start time must match the format HH:MM.',
            
            'scheduleSettings.periodDurationMinutes.required' => 'The period duration is required.',
            'scheduleSettings.periodDurationMinutes.integer' => 'The period duration must be an integer.',
            'scheduleSettings.periodDurationMinutes.min' => 'The period duration must be at least 20 minutes.',
            'scheduleSettings.periodDurationMinutes.max' => 'The period duration must not exceed 120 minutes.',
            
            'scheduleSettings.workingDays.required' => 'At least one working day is required.',
            'scheduleSettings.workingDays.array'    => 'Working days must be provided as an array.',
            'scheduleSettings.workingDays.min'      => 'There must be at least 1 working day.',
            
            'scheduleSettings.workingDays.*.day.required' => 'Each working day item must specify a day.',
            'scheduleSettings.workingDays.*.periodsCount.required' => 'Each working day must specify a periods count.',
            'scheduleSettings.workingDays.*.periodsCount.integer' => 'The periods count must be an integer.',
            'scheduleSettings.workingDays.*.periodsCount.min' => 'The periods count must be at least 1.',
            'scheduleSettings.workingDays.*.periodsCount.max' => 'The periods count must not exceed 15.',
            
            'scheduleSettings.breaks.present'        => 'The breaks field must be present.',
            'scheduleSettings.breaks.array'         => 'Breaks must be provided as an array.',
            
            'scheduleSettings.breaks.*.id.required' => 'Each break item must have an ID.',
            'scheduleSettings.breaks.*.id.string'   => 'The break ID must be a string.',
            
            'scheduleSettings.breaks.*.afterPeriodIndex.required' => 'Each break must specify after which period it occurs.',
            'scheduleSettings.breaks.*.afterPeriodIndex.integer' => 'The period index must be an integer.',
            'scheduleSettings.breaks.*.afterPeriodIndex.min' => 'The period index must be at least 1.',
            
            'scheduleSettings.breaks.*.durationMinutes.required' => 'Each break must specify a duration in minutes.',
            'scheduleSettings.breaks.*.durationMinutes.integer' => 'The break duration must be an integer.',
            'scheduleSettings.breaks.*.durationMinutes.min' => 'The break duration must be at least 5 minutes.',
        ];
    }

}
