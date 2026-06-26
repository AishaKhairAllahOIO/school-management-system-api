<?php

namespace App\Http\Requests\Setting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\BaseRequest;



class UpdateAcademicSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('super_admin');
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
            'passingGrade'                => 'required|string',
            'maximumGrade'                => 'required|integer',
            'gpaScale'                    => 'required|in:4.0,5.0,100',
            'minimumAttendancePercentage' => 'required|integer|min:0|max:100',
            'promotionThreshold'          => 'required|integer',

            'preferences'                          => 'array',
            'preferences.autoPromoteStudents'      => 'boolean',
            'preferences.allowStudentRepeating'    => 'boolean',
            'preferences.calculateGpa'             => 'boolean',
            'preferences.rankStudents'             => 'boolean',
            'preferences.useAttendanceInPromotion' => 'boolean',

            'academicYears'               => 'array',
            'academicYears.*.id'          => 'nullable', 
            'academicYears.*.name'        => 'required|string',
            'academicYears.*.startDate'   => 'required|date',
            'academicYears.*.endDate'     => 'required|date|after:academicYears.*.startDate',

            'terms'                       => 'array',
            'terms.*.id'                  => 'nullable',
            'terms.*.name'                => 'required|string|distinct',
            'terms.*.startDate'           => 'required|date',
            'terms.*.endDate'             => 'required|date|after:terms.*.startDate',

            'gradeScale'                  => 'array',
            'gradeScale.*.id'             => 'nullable',
            'gradeScale.*.grade'          => 'required|string|distinct',
            'gradeScale.*.minimumScore'   => 'required|integer|min:0',
            'gradeScale.*.maximumScore'   => 'required|integer|gt:gradeScale.*.minimumScore',
            'gradeScale.*.description'    => 'nullable|string',
        ];
    }
    public function messages(){
    return [
        'currentAcademicYearId.required' => 'The academic year must be selected.',
        'currentAcademicYearId.exists'   => 'The selected academic year is invalid.',
        'passingGrade.required'          => 'The passing grade is required.',
        'maximumGrade.integer'           => 'Maximum grade must be a valid number.',
        'gpaScale.in'                    => 'GPA scale must be one of the following: 4.0, 5.0, or 100.',
        'minimumAttendancePercentage.between' => 'Attendance must be between 0 and 100%.',

        'academicYears.*.name.required'      => 'Academic year name is required for all entries.',
        'academicYears.*.startDate.required' => 'Start date is required for all academic years.',
        'academicYears.*.endDate.after'      => 'End date must be after the start date for all academic years.',

        'terms.*.name.required'   => 'Term name is required.',
        'terms.*.status.in'       => 'Status must be active, upcoming, or completed.',
        'terms.*.endDate.after'   => 'Term end date must be after the start date.',

        // سلم الدرجات
        'gradeScale.*.grade.required'        => 'Grade letter is required.',
        'gradeScale.*.minimumScore.required' => 'Minimum score is required.',
        'gradeScale.*.maximumScore.gt'       => 'The maximum score must be strictly greater than the minimum score.',
    ];
}
    // public function attributes(): array
    // {
    //     return [
    //         'currentAcademicYearId' => 'Academic Year',
    //         'passingGrade'          => 'Passing Grade',
    //         'gradeScale.*.grade'    => 'Grade',
    //         'gradeScale.*.minimumScore' => 'Minimum Score',
    //         'gradeScale.*.maximumScore' => 'Maximum Score',
    //     ];
    // }
}
