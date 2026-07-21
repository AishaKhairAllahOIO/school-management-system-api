<?php

namespace App\Services\Setting;

use App\Models\GradeSubject;
use Illuminate\Database\Eloquent\Collection;

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
        $gradeSubject->delete();
    }

}
