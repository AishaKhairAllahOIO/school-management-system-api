<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExamSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'grade_subject_id',
        'exam_date',
        'start_time',
        'end_time',
        'syllabus',
    ];

    protected $casts = [
        'exam_date' => 'date',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'exam_subject_teacher', 'exam_subject_id', 'staff_id')
                    ->withTimestamps();
    }
}