<?php

namespace App\Services\Setting;

use App\Models\GradeSubject;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class GradeSubjectService
{

    public function getAllGradeSubjects(): Collection
    {
        return GradeSubject::with(['academicYear', 'semester', 'gradeLevel', 'subject'])
            ->orderBy('grade_level_id')
            ->get();

    }

    public function createGradeSubject(array $data): GradeSubject
    {
        return GradeSubject::create($data);
    }


    public function updateGradeSubject(GradeSubject $gradeSubject, array $data): GradeSubject
    {
        $gradeSubject->update($data);
        return $gradeSubject;
    }

    public function deleteGradeSubject(GradeSubject $gradeSubject): void
    {
        if (
            $gradeSubject->assessmentComponents()->exists() ||
            $gradeSubject->teacherAssignments()->exists() ||
            $gradeSubject->homeworks()->exists() ||
            $gradeSubject->reportCardDetails()->exists()
        ) {
            throw new ValidationException(
                'Cannot delete this grade subject because it is linked to other records.'
            );
        }

        $gradeSubject->delete();
    }

}
