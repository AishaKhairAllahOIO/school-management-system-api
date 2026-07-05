<?php

namespace App\Services\Setting;

use App\ApiResource;
use App\Models\AcademicSetting;
use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\GradeLevel;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException; // 👈 لا تنسي استدعاء هذه الكلاس في أعلى الملف
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\AcademicStage;



class AcademicSettingsService
{

    use ApiResource;
 public function getAcademicViewData()
    {
    $settings = AcademicSetting::first();

   return  $settings;
    }
     public function getAllYears(): Collection
    {
        return AcademicYear::orderBy('start_date', 'desc')->get();
    }

    public function getYearById(int $id): AcademicYear
    {
        return AcademicYear::findOrFail($id);
    }

    public function getAllTerms(): Collection
    {
        return Semester::orderBy('academic_year_id', 'desc')->orderBy('order', 'asc')->get();
    }

    public function getTermById(int $id): Semester
    {
        return Semester::findOrFail($id);
    }

    public function getAllStages(): Collection
    {
        return AcademicStage::all();
    }

    public function getStageById(int $id): AcademicStage
    {
        return AcademicStage::findOrFail($id);
    }

    // تحديث إعدادات الجدولة
    public function updateSettings(array $data)
    {
        return DB::transaction(function () use ($data) {
        $settings = AcademicSetting::updateOrCreate(
            ['id' => 1],
            [
                'current_academic_year_id' => $data['currentAcademicYearId'] ?? null,
                'current_semester_id' => $data['currentSemesterId'] ?? null,
                'schedule_settings'        => $data['scheduleSettings'],
            ] 
        );

            return $settings->refresh();
        });
    }

    // ---------------- عمليات العام الدراسي ----------------

    public function saveYear(array $data, ?AcademicYear $year = null)
    {
        return DB::transaction(function () use ($data, $year):AcademicYear {
            
            $startYear = isset($data['startDate']) ? Carbon::parse($data['startDate'])->year : Carbon::parse($year->start_date)->year;
            $endYear   = isset($data['endDate']) ? Carbon::parse($data['endDate'])->year : Carbon::parse($year->end_date)->year;
            
            $yearName = "{$startYear}-{$endYear}";

            $existingYear = AcademicYear::where('year_name', $yearName)
                ->when($year, fn($query) => $query->where('id', '!=', $year->id)) // تجاهل السنة الحالية لو كنا في حالة تعديل
                ->exists();

            if ($existingYear) {
                throw ValidationException::withMessages([
                    'startDate' => ["العام الدراسي ({$yearName}) مُسجل مسبقاً في النظام ولا يمكن تكراره."]
                ]);
            }

            if (isset($data['isCurrent']) && $data['isCurrent']) {
                AcademicYear::query()->update(['is_current' => false]); 
            }

            $payload = [
                'year_name'  => $yearName,
                'start_date' => $data['startDate'] ?? $year->start_date,
                'end_date'   => $data['endDate'] ?? $year->end_date,
                'is_current' => $data['isCurrent'] ?? $year->is_current,
            ];

            if ($year) {
                $year->update($payload);
            } else {
                $year = AcademicYear::create($payload);
            }

            return $year->refresh();
        });
    }
    public function getCurrentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_current', true)->first();
    }

    // ---------------- عمليات الفصل الدراسي ----------------
private function determineTermNameAndOrder(string $inputName): array
    {
        $nameLower = strtolower($inputName);
        
        if (str_contains($nameLower, 'first') || str_contains($nameLower, 'أول') || $nameLower === '1') {
            return ['name' => Semester::FIRST_TERM, 'order' => 1];
        } 
        
        if (str_contains($nameLower, 'second') || str_contains($nameLower, 'ثاني') || $nameLower === '2') {
            return ['name' => Semester::SECOND_TERM, 'order' => 2];
        }

        // قيمة افتراضية في حال تم إدخال اسم غريب جداً
        return ['name' => $inputName, 'order' => 1]; 
    }

    public function saveTerm(array $data, ?Semester $term = null): Semester 
    {
        return DB::transaction(function () use ($data, $term) {
            
            $academicYearId = $data['academicYearId'] ?? $term->academic_year_id;
            
            // 👈 أخذ الاسم المرسل من الفرونت إند
            $inputSemesterName = $data['semesterName'] ?? $term->semester_name;

            // 🧠 تمرير الاسم للعقل المدبر لاستنتاج الترتيب والاسم الموحد
            $termData = $this->determineTermNameAndOrder($inputSemesterName);
            $semesterName = $termData['name'];
            $order = $termData['order'];

            $existingTerm = Semester::where('academic_year_id', $academicYearId)
                ->where('semester_name', $semesterName)
                ->when($term, fn($query) => $query->where('id', '!=', $term->id)) // تجاهل الفصل الحالي عند التعديل
                ->exists();

            if ($existingTerm) {
                throw ValidationException::withMessages([
                    'semesterName' => ["الفصل الدراسي المماثل مسجل مسبقاً في هذا العام الدراسي ولا يمكن تكراره."]
                ]);
            }

            if (isset($data['isCurrent']) && $data['isCurrent']) {
                Semester::query()->update(['is_current' => false]); // إطفاء البقية
            }

            $payload = [
                'academic_year_id' => $academicYearId,
                'semester_name'    => $semesterName, // 👈 الاسم الموحد والمستنتج آلياً
                'start_date'       => $data['startDate'] ?? $term->start_date,
                'end_date'         => $data['endDate'] ?? $term->end_date,
                'order'            => $order,        // 👈 الترتيب المستنتج آلياً (تجاهلنا الفرونت إند تماماً هنا)
                'is_current'       => $data['isCurrent'] ?? $term->is_current,
                'is_final_term'    => $data['isFinalTerm'] ?? $term->is_final_term,
            ];

            if ($term) {
                $term->update($payload);
            } else {
                $term = Semester::create($payload);
            }

            return $term->refresh(); 
        });
    }

    // ---------------- عمليات المراحل الدراسية ----------------
    public function saveStage(array $data, ?AcademicStage $stage = null)
    {
        if ($stage) {
            $stage->update(['type' => $data['type']]);
        } else {
            $stage = AcademicStage::create(['type' => $data['type']]);
        }
        return $stage;
    }


  public function deleteYear(int $id): void
    {
        $year = AcademicYear::findOrFail($id);
        
        $hasSemesters = Semester::where('academic_year_id', $id)->exists();
        $hasConfigurations = \App\Models\GradeConfiguration::where('academic_year_id', $id)->exists();
        
        if ($hasSemesters || $hasConfigurations) {
            throw new HttpResponseException(
                $this->errorResponse('لا يمكن حذف العام الدراسي لارتباطه بفصول دراسية أو إعدادات تخطيطية.', 409)
            );
        }
        
        $year->delete();
    }

    public function deleteTerm(int $id): void
    {
        $term = Semester::findOrFail($id);
        $term->delete();
    }

    public function deleteStage(int $id): void
    {
        $stage = AcademicStage::findOrFail($id);
        $hasGrades = GradeLevel::where('academic_stage_id', $id)->exists();
        
        if ($hasGrades) {
            throw new HttpResponseException(
                $this->errorResponse('لا يمكن حذف المرحلة الدراسية لأنها تحتوي على صفوف دراسية. احذف الصفوف أولاً.', 409)
            );
        }
        
        $stage->delete();
    }

    // public function syncSettings(array $data)
    // {
    //     return DB::transaction(function () use ($data) {

    //         if (!empty($data['academicYears'])) {
    //             foreach ($data['academicYears'] as $yearData) {
    //                 AcademicYear::updateOrCreate(
    //                     ['id' => $yearData['id'] ?? null], // إذا لم يكن هناك ID، قم بإنشاء سجل جديد
    //                     [
    //                         'year_name'  => $yearData['name'],
    //                         'start_date' => $yearData['startDate'],
    //                         'end_date'   => $yearData['endDate'],
    //                     ]
    //                 );
    //             }
    //         }

    //         if (!empty($data['terms'])) {
    //             foreach ($data['terms'] as $termData) {
    //                 $academicYearId = $termData['academic_year_id'] ?? ($data['academicYears'][0]['id'] ?? null);
    //                 Semester::updateOrCreate(
    //                     ['id' => $termData['id'] ?? null],
    //                     [
    //                         'academic_year_id' => $academicYearId, // تأكد من ربط الفصل بالسنة الدراسية الصحيحة
    //                         'semester_name' => $termData['name'],
    //                         'start_date'    => $termData['startDate'],
    //                         'end_date'      => $termData['endDate'],
    //                     ]
    //                 );
    //             }
    //         }

    //         $settings = AcademicSetting::updateOrCreate(
    //             ['school_id' => 1],
    //             [
    //                 'current_academic_year_id'      => $data['currentAcademicYearId'],
    //                 'passing_grade'                 => $data['passingGrade'],
    //                 'maximum_grade'                 => $data['maximumGrade'],
    //                 'gpa_scale'                     => $data['gpaScale'],
    //                 'minimum_attendance_percentage' => $data['minimumAttendancePercentage'],
    //                 'promotion_threshold'           => $data['promotionThreshold'],

    //                 'auto_promote_students'         => $data['preferences']['autoPromoteStudents'],
    //                 'allow_student_repeating'       => $data['preferences']['allowStudentRepeating'],
    //                 'calculate_gpa'                 => $data['preferences']['calculateGpa'],
    //                 'rank_students'                 => $data['preferences']['rankStudents'],
    //                 'use_attendance_in_promotion'   => $data['preferences']['useAttendanceInPromotion'],
    //             ]
    //         );

    //         if (!empty($data['gradeScale'])) {

    //             $providedIds = collect($data['gradeScale'])->pluck('id')->filter()->toArray();
    //             $settings->gradeScales()->whereNotIn('id', $providedIds)->delete();
    //             // $scalesToDelete=$settings->gradeScales()->whereNotIn('id', $providedIds)->get();
    //             // if($scalesToDelete->isNotEmpty()){
    //             //     $idToDelete=$scalesToDelete->pluck('id')->toArray();
    //             //     $isUsed=StudenGrade::whereIn('grade_scale_id', $idToDelete)->exists();
    //             //     if($isUsed){
    //             //         throw new Exception('Cannot delete grade scales that are currently in use.');
    //             //     }
    //             //     $settings->gradeScales()->whereIn('id', $idToDelete)->delete();
    //             // }
    //             foreach ($data['gradeScale'] as $gradeData) {
    //                 $settings->gradeScales()->updateOrCreate(
    //                     ['id' => $gradeData['id'] ?? null],
    //                     [
    //                         'grade'        => $gradeData['grade'],
    //                         'minimum_score' => $gradeData['minimumScore'],
    //                         'maximum_score' => $gradeData['maximumScore'],
    //                         'description'   => $gradeData['description'] ?? null,
    //                     ]
    //                 );
    //             }
    //         }

    //         return $settings->load(['gradeScales']);
    //     });
    // }





    // private function loadLevels(array $ids): Collection
    // {
    //     return GradeLevel::query()
    //         ->whereIn('id', $ids)
    //         ->withCount('classRooms')
    //         ->with(['classRooms:id,grade_level_id,name,capacity'])
    //         ->orderBy('id')
    //         ->get();
    // }



// public function deleteLevel(int $id): void
// {
//     $level = GradeLevel::withCount('classRooms')->findOrFail($id);

//     // حماية: امنع حذف مرحلة فيها شعب
//     if ($level->class_rooms_count > 0) {
//         throw new HttpResponseException(
//             $this->errorResponse(
//                 'لا يمكن حذف المرحلة لأنها تحتوي على شعب. احذف الشعب أولاً.',
//                 409 // Conflict
//             )
//         );
//     }

//     $level->delete();
// }

// public function updateClassroom(int $id, array $data): ClassRoom
// {
//     $classRoom = ClassRoom::findOrFail($id);
//     $classRoom->update($data);

//     return $classRoom->fresh();
// }

// public function deleteClassroom(int $id): void
// {
//     $classRoom = ClassRoom::withCount('enrollments')->findOrFail($id);

//     if ($classRoom->enrollments_count > 0) {
//         throw new HttpResponseException(
//             $this->errorResponse(
//                 'لا يمكن حذف الشعبة لأنها تحتوي على طلاب مسجّلين.',
//                 409
//             )
//         );
//     }

//     $classRoom->delete();
// }

}
