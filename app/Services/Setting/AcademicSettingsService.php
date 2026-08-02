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
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\AcademicStage;
use App\Models\Enrollment;
use Exception;
use App\Models\GradeConfiguration;
use App\Models\Subject;

class AcademicSettingsService
{

    use ApiResource;
    public function getAcademicViewData()
    {
        $settings = AcademicSetting::firstOrCreate(
            ['id' => 1],
            [
                'current_academic_year_id' => null,
                'current_semester_id' => null,
                'schedule_settings' => json_encode([]),
            ]
        );

        return $settings;
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

    public function updateSettings(array $data)
    {
        return DB::transaction(function () use ($data) {
            $settings = AcademicSetting::updateOrCreate(
                ['id' => 1],
                [
                    'current_academic_year_id' => $data['currentAcademicYearId'] ?? null,
                    'current_semester_id' => $data['currentSemesterId'] ?? null,
                    'schedule_settings' => $data['scheduleSettings'],
                ]
            );

            return $settings->refresh();
        });
    }


    public function saveYear(array $data, ?AcademicYear $year = null)
    {
        return DB::transaction(function () use ($data, $year): AcademicYear {

            $startYear = isset($data['startDate']) ? Carbon::parse($data['startDate'])->year : Carbon::parse($year->start_date)->year;
            $endYear = isset($data['endDate']) ? Carbon::parse($data['endDate'])->year : Carbon::parse($year->end_date)->year;

            $yearName = "{$startYear}-{$endYear}";

            $existingYear = AcademicYear::where('year_name', $yearName)
                ->when($year, fn($query) => $query->where('id', '!=', $year->id))
                ->exists();

            if ($existingYear) {
                throw ValidationException::withMessages([
                    'startDate' => ["The academic year ({$yearName}) is already registered and cannot be duplicated."]
                ]);
            }

            if (isset($data['isCurrent']) && $data['isCurrent']) {
                AcademicYear::query()->update(['is_current' => false]);
            }

            $payload = [
                'year_name' => $yearName,
                'start_date' => $data['startDate'] ?? $year->start_date,
                'end_date' => $data['endDate'] ?? $year->end_date,
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

    private function determineTermNameAndOrder(string $inputName): array
    {
        $nameLower = strtolower($inputName);

        if (str_contains($nameLower, 'first') || str_contains($nameLower, 'أول') || $nameLower === '1') {
            return ['name' => Semester::FIRST_TERM, 'order' => 1];
        }

        if (str_contains($nameLower, 'second') || str_contains($nameLower, 'ثاني') || $nameLower === '2') {
            return ['name' => Semester::SECOND_TERM, 'order' => 2];
        }

        return ['name' => $inputName, 'order' => 1];
    }

    public function saveTerm(array $data, ?Semester $term = null): Semester
    {
        return DB::transaction(function () use ($data, $term) {

            $academicYearId = $data['academicYearId'] ?? $term->academic_year_id;

            $inputSemesterName = $data['semesterName'] ?? $term->semester_name;

            $termData = $this->determineTermNameAndOrder($inputSemesterName);
            $semesterName = $termData['name'];
            $order = $termData['order'];

            $existingTerm = Semester::where('academic_year_id', $academicYearId)
                ->where('semester_name', $semesterName)
                ->when($term, fn($query) => $query->where('id', '!=', $term->id))
                ->exists();

            if ($existingTerm) {
                throw ValidationException::withMessages([
                    'semesterName' => ["The semester name ({$semesterName}) is already registered for the selected academic year and cannot be duplicated."]
                ]);
            }

            if (isset($data['isCurrent']) && $data['isCurrent']) {
                Semester::query()->update(['is_current' => false]);
            }

            $payload = [
                'academic_year_id' => $academicYearId,
                'semester_name' => $semesterName,
                'start_date' => $data['startDate'] ?? $term->start_date,
                'end_date' => $data['endDate'] ?? $term->end_date,
                'order' => $order,
                'is_current' => $data['isCurrent'] ?? $term->is_current,
                'is_final_term' => $data['isFinalTerm'] ?? $term->is_final_term,
            ];

            if ($term) {
                $term->update($payload);
            } else {
                $term = Semester::create($payload);
            }

            return $term->refresh();
        });
    }

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
        if (!$year) {
            throw new ModelNotFoundException("The specified academic year does not exist.");
        }

        $hasSemesters = Semester::where('academic_year_id', $id)->exists();
        $hasConfigurations = GradeConfiguration::where('academic_year_id', $id)->exists();

        if ($hasSemesters || $hasConfigurations) {
            throw new Exception(
                'The specified academic year cannot be deleted because it contains semesters or grade configurations.',
                409
            )
            ;
        }

        $year->delete();
    }

    public function deleteTerm(int $id): void
    {
        $term = Semester::findOrFail($id);
        if (!$term) {
            throw new ModelNotFoundException("The specified semester does not exist.");
        }
        if ($term->is_current) {
            throw new Exception('The current semester cannot be deleted.', 409);
        }

        $term->delete();
    }

    public function deleteStage(int $id): void
    {
        $stage = AcademicStage::findOrFail($id);
        if (!$stage)
            throw new ModelNotFoundException("The specified academic stage does not exist.");
        $hasGrades = GradeLevel::where('academic_stage_id', $id)->exists();

        if ($hasGrades) {
            throw new Exception(
                'The specified academic stage cannot be deleted because it contains grade levels. Please delete the grade levels first.',
                409
            )
            ;
        }

        $stage->delete();
    }
    public function deleteSettings(): void
    {
        $settings = AcademicSetting::findOrFail(1);

        if ($settings) {
            if ($settings->current_academic_year_id) {
                $hasEnrollments = Enrollment::where('academic_year_id', $settings->current_academic_year_id)->exists();

                if ($hasEnrollments) {
                    throw new Exception(
                        'Warning: The academic settings for the school cannot be deleted because there are students enrolled based on these settings.',
                        409
                    );
                }
            }


            $settings->delete();
        }
    }

    public function getAcademicStatistics(): array
    {
        return [
            'subjectsCount' => Subject::count(),
            'gradeLevelsCount' => GradeLevel::count(),
            'classRoomsCount' => ClassRoom::count(),
        ];
    }




}
