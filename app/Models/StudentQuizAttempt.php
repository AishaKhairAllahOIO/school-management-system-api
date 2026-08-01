<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_quiz_id',
        'enrollment_id',
        'total_mark',
        'earned_mark',
    ];

    protected $casts = [
        'total_mark'  => 'float',
        'earned_mark' => 'float',
    ];

    public function practiceQuiz(): BelongsTo
    {
        return $this->belongsTo(PracticeQuiz::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function attemptAnswers()
    {
        return $this->hasMany(StudentQuizAttemptAnswer::class, 'student_quiz_attempt_id');
    }
}
