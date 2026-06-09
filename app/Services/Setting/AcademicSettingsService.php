<?php

namespace App\Services\Setting;

use App\Models\AcademicSetting;
use App\Models\AcademicYear;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class AcademicSettingsService
{
    public function syncSettings(array $data)
    {
        return DB::transaction(function () use ($data) {
            
            if (!empty($data['academicYears'])) {
                foreach ($data['academicYears'] as $yearData) {
                    AcademicYear::updateOrCreate(
                        ['id' => $yearData['id'] ?? null], // إذا لم يكن هناك ID، قم بإنشاء سجل جديد
                        [
                            'year_name'  => $yearData['name'],
                            'start_date' => $yearData['startDate'],
                            'end_date'   => $yearData['endDate'],
                        ]
                    );
                }
            }

            if (!empty($data['terms'])) {
                foreach ($data['terms'] as $termData) {
                $academicYearId = $termData['academic_year_id'] ?? ($data['academicYears'][0]['id'] ?? null);
                    Semester::updateOrCreate(
                        [  'id' => $termData['id'] ?? null],
                        [   'academic_year_id'=>$academicYearId, // تأكد من ربط الفصل بالسنة الدراسية الصحيحة
                            'semester_name' => $termData['name'],
                            'start_date'    => $termData['startDate'],
                            'end_date'      => $termData['endDate'],
                        ]
                    );
                }
            }

            $settings = AcademicSetting::updateOrCreate(
                ['school_id' => 1],
                [
                    'current_academic_year_id'      => $data['currentAcademicYearId'],
                    'passing_grade'                 => $data['passingGrade'],
                    'maximum_grade'                 => $data['maximumGrade'],
                    'gpa_scale'                     => $data['gpaScale'],
                    'minimum_attendance_percentage' => $data['minimumAttendancePercentage'],
                    'promotion_threshold'           => $data['promotionThreshold'],
                    
                    'auto_promote_students'         => $data['preferences']['autoPromoteStudents'],
                    'allow_student_repeating'       => $data['preferences']['allowStudentRepeating'],
                    'calculate_gpa'                 => $data['preferences']['calculateGpa'],
                    'rank_students'                 => $data['preferences']['rankStudents'],
                    'use_attendance_in_promotion'   => $data['preferences']['useAttendanceInPromotion'],
                ]
            );

            if (!empty($data['gradeScale'])) {
               
            $providedIds = collect($data['gradeScale'])->pluck('id')->filter()->toArray();
            $settings->gradeScales()->whereNotIn('id', $providedIds)->delete(); 
            // $scalesToDelete=$settings->gradeScales()->whereNotIn('id', $providedIds)->get(); 
            // if($scalesToDelete->isNotEmpty()){
            //     $idToDelete=$scalesToDelete->pluck('id')->toArray();
            //     $isUsed=StudenGrade::whereIn('grade_scale_id', $idToDelete)->exists();
            //     if($isUsed){
            //         throw new Exception('Cannot delete grade scales that are currently in use.');
            //     }
            //     $settings->gradeScales()->whereIn('id', $idToDelete)->delete();
            // }      
            foreach ($data['gradeScale'] as $gradeData) {
                    $settings->gradeScales()->updateOrCreate(
                        ['id' => $gradeData['id'] ?? null],
                        [
                            'grade'        => $gradeData['grade'],
                            'minimum_score' => $gradeData['minimumScore'],
                            'maximum_score' => $gradeData['maximumScore'],
                            'description'   => $gradeData['description'] ?? null,
                        ]
                    );
                }
        
            }

            return $settings->load(['gradeScales']); 
        });
    }
}