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
use Illuminate\Pagination\LengthAwarePaginator;


class AcademicSettingsService
{

    use ApiResource;
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
                        ['id' => $termData['id'] ?? null],
                        [
                            'academic_year_id' => $academicYearId, // تأكد من ربط الفصل بالسنة الدراسية الصحيحة
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


    public function createStructure(array $data): Collection
    {
        return DB::transaction(function () use ($data) {
            $ids = [];

            foreach ($data['grade_levels'] as $levelData) {
                $ids[] = $this->persistLevel($levelData)->id;
            }

            return $this->loadLevels($ids);
        });
    }

    public function createSingleLevel(array $levelData): GradeLevel
    {
        return DB::transaction(function () use ($levelData) {
            $level = $this->persistLevel($levelData);

            return $this->loadLevels([$level->id])->first();
        });
    }

    private function persistLevel(array $levelData): GradeLevel
    {
        $level = GradeLevel::create(['grade_name' => $levelData['grade_name']]);

        $now  = now();
        $rows = [];

        foreach ($levelData['classrooms'] as $index => $classroom) {
            $rows[] = [
                'grade_level_id' => $level->id,
                'name'           => $classroom['name'] ?? $this->generateClassroomName($index),
                'capacity'       => $classroom['capacity'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        ClassRoom::insert($rows); // كل الشعب باستعلام واحد

        return $level;
    }

    private function loadLevels(array $ids): Collection
    {
        return GradeLevel::query()
            ->whereIn('id', $ids)
            ->withCount('classRooms')
            ->with(['classRooms:id,grade_level_id,name,capacity'])
            ->orderBy('id')
            ->get();
    }

    private function generateClassroomName(int $index): string
    {
        $letters = ['أولى', 'ثانية', 'ثالثة', 'رابعة', 'خامسة', 'سادسة', 'سابعة', 'ثامنة', 'تاسعة', 'عاشرة'];

        return 'شعبة ' . ($letters[$index] ?? (string) ($index + 1));
    }

    public function listStructure(): LengthAwarePaginator
    {
        return GradeLevel::query()
            ->withCount('classRooms')
            ->with(['classRooms:id,grade_level_id,name,capacity'])
            ->orderBy('id')
            ->paginate(15);
    }

    public function showLevel(int $id): GradeLevel
{
    // loadLevels تعيد Collection — نأخذ أول عنصر، أو نرمي 404
    $level = $this->loadLevels([$id])->first();

    return $level;
}

public function updateLevel(int $id, array $data): GradeLevel
{
    $level = GradeLevel::findOrFail($id);
    $level->update(['grade_name' => $data['grade_name']]);

    return $this->loadLevels([$level->id])->first();
}

public function deleteLevel(int $id): void
{
    $level = GradeLevel::withCount('classRooms')->findOrFail($id);

    // حماية: امنع حذف مرحلة فيها شعب
    if ($level->class_rooms_count > 0) {
        throw new HttpResponseException(
            $this->errorResponse(
                'لا يمكن حذف المرحلة لأنها تحتوي على شعب. احذف الشعب أولاً.',
                409 // Conflict
            )
        );
    }

    $level->delete();
}

public function updateClassroom(int $id, array $data): ClassRoom
{
    $classRoom = ClassRoom::findOrFail($id);
    $classRoom->update($data);

    return $classRoom->fresh();
}

public function deleteClassroom(int $id): void
{
    $classRoom = ClassRoom::withCount('enrollments')->findOrFail($id);

    if ($classRoom->enrollments_count > 0) {
        throw new HttpResponseException(
            $this->errorResponse(
                'لا يمكن حذف الشعبة لأنها تحتوي على طلاب مسجّلين.',
                409
            )
        );
    }

    $classRoom->delete();
}

}
