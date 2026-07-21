<?php

namespace App\Services\Setting;

use App\Models\Subject;

class SubjectService
{

    public function createSubject(array $data): Subject
    {
        return Subject::create([
            'subject_name' => $data['subject_name']
        ]);
    }


    public function updateSubject(Subject $subject, array $data): Subject
    {
        $subject->update([
            'subject_name' => $data['subject_name'] ?? $subject->subject_name,
        ]);

        return $subject;
    }
}
