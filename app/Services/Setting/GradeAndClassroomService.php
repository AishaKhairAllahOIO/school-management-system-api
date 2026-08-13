<?php

namespace App\Services\Setting;

use App\Models\GradeLevel;
use App\Models\GradeConfiguration;
use App\Models\Classroom;
use App\Models\Enrollment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;



class GradeAndClassroomService
{

    private function determineLevelFromName(string $name): int
    {

        if(str_contains($name, 'first')) return 1;
        if(str_contains($name, 'second')) return 2;
        if(str_contains($name, 'third')) return 3;
        if(str_contains($name, 'fourth')) return 4;
        if(str_contains($name, 'fifth')) return 5;
        if(str_contains($name, 'sixth')) return 6;
        if (str_contains($name, 'seventh')) return 7;
        if (str_contains($name, 'eighth')) return 8;
        if (str_contains($name, 'ninth')) return 9;
        if (str_contains($name, 'tenth')) return 10;
        if (str_contains($name, 'eleventh')) return 11;
        if (str_contains($name, 'twelfth')) return 12;


        return 1;
    }



    public function createGrade(array $data)
    {
        $name = $data['name'];
        $level = $this->determineLevelFromName($name);

        return GradeLevel::updateOrCreate([
            'name' => $name
        ],[
            'academic_stage_id'   => $data['academicStageId'],
            'name'                => $name,
            'level'               => $level,
            'is_graduation_grade' => $data['isGraduationGrade'] ?? false,
        ]);
    }

    public function updateGrade(GradeLevel $grade, array $data)
    {
        $name = $data['name'] ?? $grade->name;
        $level = $this->determineLevelFromName($name);

        $grade->update([
            'academic_stage_id'   => $data['academicStageId'] ?? $grade->academic_stage_id,
            'name'                => $name,
            'level'               => $level,
            'is_graduation_grade' => $data['isGraduationGrade'] ?? $grade->is_graduation_grade,
        ]);

        return $grade->fresh();
    }


    public function createConfiguration(array $data)
    {
        return GradeConfiguration::create([
            'academic_year_id'         => $data['academicYearId'],
            'grade_level_id'           => $data['grade_level_id'],
            'supervisor_id'            => $data['supervisor_id'] ?? null,
            'planned_classrooms_count' => $data['planned_classrooms_count'],
        ]);
    }

    public function updateConfiguration(GradeConfiguration $config, array $data)
    {
        $config->update([
            'grade_level_id'           => $data['grade_level_id'] ?? $config->grade_level_id,
            'supervisor_id'            => $data['supervisor_id'] ?? $config->supervisor_id,
            'planned_classrooms_count' => $data['planned_classrooms_count'] ?? $config->planned_classrooms_count,
        ]);

        return $config->fresh();
    }

    public function createClassroom(array $data)
    {
        return DB::transaction(function () use ($data) {
            $yearId = $data['academicYearId'];
            $gradeId = $data['grade_level_id'];
            $capacity = $data['capacity'];

            $currentCount = Classroom::where('academic_year_id', $yearId)
                                     ->where('grade_level_id', $gradeId)
                                     ->count();

            $name = "الشعبة " . ($currentCount + 1);

            $classroom = Classroom::create([
                'academic_year_id' => $yearId,
                'grade_level_id'   => $gradeId,
                'name'             => $name,
                'capacity'         => $capacity,
            ]);

            $this->recalculateGradeCapacity($yearId, $gradeId);


            return $classroom;
        });
    }

    public function updateClassroom(Classroom $classroom, array $data)
    {
        return DB::transaction(function () use ($classroom, $data) {
            $classroom->update([
                'grade_level_id' => $data['grade_level_id'] ?? $classroom->grade_level_id,
                'capacity' => $data['capacity'] ?? $classroom->capacity,
            ]);

            $this->recalculateGradeCapacity($classroom->academic_year_id, $classroom->grade_level_id);

            return $classroom->fresh();
        });
    }


       public function getGradeById(int $id): GradeLevel
    {
        return GradeLevel::findOrFail($id);
    }

    public function getConfigurationById(int $id): GradeConfiguration
    {
        return GradeConfiguration::findOrFail($id);
    }

    public function getClassroomById(int $id): Classroom
    {
        return Classroom::findOrFail($id);
    }
       public function deleteGrade(int $id)
    {
        $grade = GradeLevel::findOrFail($id);
        if(!$grade)
            throw new ModelNotFoundException("The grade level you are looking for does not exist.",404);
        $hasClassrooms = Classroom::where('grade_level_id', $id)->exists();
        $hasConfigs = GradeConfiguration::where('grade_level_id', $id)->exists();

        if ($hasClassrooms || $hasConfigs) {
            throw new Exception("The grade level you are looking for cannot be deleted because it has associated classrooms or configurations.", 409);
        }

        $grade->delete();
    }

    public function deleteConfiguration(int $id): void
    {
        $config = GradeConfiguration::findOrFail($id);
        if(!$config) {
            throw new ModelNotFoundException("The grade configuration you are looking for does not exist.",404);
        }
        $hasClassrooms = Classroom::where('academic_year_id', $config->academic_year_id)
                                  ->where('grade_level_id', $config->grade_level_id)
                                  ->exists();

        if ($hasClassrooms) {
            throw new Exception(
              'The grade configuration you are looking for cannot be deleted because it has associated classrooms.'
            , 409);
        }

        $hasEnrollments = Enrollment::where('academic_year_id', $config->academic_year_id)
                                                ->where('grade_level_id', $config->grade_level_id)
                                                ->exists();

        if ($hasEnrollments) {
            throw new Exception( 'The grade configuration you are looking for cannot be deleted because it has associated enrollments.'
            , 409);
        }
        $config->delete();
    }

    public function deleteClassroom(int $id): void
    {
        $classroom = Classroom::findOrFail($id);
        if(!$classroom) {
            throw new ModelNotFoundException("The classroom you are looking for does not exist.",404);
        }
        $yearId = $classroom->academic_year_id;
        $gradeId = $classroom->grade_level_id;

        $hasStudents = Enrollment::where('class_room_id', $classroom->id)->exists();

        if ($hasStudents) {
            throw new Exception(
             'The classroom you are looking for cannot be deleted because it has associated students.'
            , 409);
        }
        $classroom->delete();

        $this->recalculateGradeCapacity($yearId, $gradeId);
    }

    private function recalculateGradeCapacity($yearId, $gradeId): void
    {
        $totalCapacity = Classroom::where('academic_year_id', $yearId)
                                  ->where('grade_level_id', $gradeId)
                                  ->sum('capacity');

        GradeConfiguration::where('academic_year_id', $yearId)
                          ->where('grade_level_id', $gradeId)
                          ->update(['planned_students_capacity' => $totalCapacity]);
    }
     public function getAllGrades()
    {
        return GradeLevel::orderBy('level', 'asc')->get();
    }

    public function getAllConfigurations()
    {
        return GradeConfiguration::latest()->get();
    }

    public function getAllClassrooms()
    {
        return Classroom::latest()->get();
    }
}
