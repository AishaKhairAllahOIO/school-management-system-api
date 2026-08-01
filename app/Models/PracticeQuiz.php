<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PracticeQuiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_subject_id',
        'teacher_id',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(StudentQuizAttempt::class);
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'practice_quiz_user_reads', 'practice_quiz_id', 'user_id')
            ->withPivot('read_at');
    }
}
