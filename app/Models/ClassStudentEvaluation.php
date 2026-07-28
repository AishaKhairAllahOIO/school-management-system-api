<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassStudentEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'enrollment_id',
        'grade_subject_id',
        'rating',
        'notes',
    ];

    const RATING_EXCELLENT = 'excellent';
    const RATING_VERY_GOOD = 'very_good';
    const RATING_GOOD      = 'good';
    const RATING_AVARAGE= 'average';
    const RATING_WEAK      = 'weak';

    public static function getAvailableRatings(): array
    {
        return [
            self::RATING_EXCELLENT,
            self::RATING_VERY_GOOD,
            self::RATING_GOOD,
            self::RATING_AVARAGE,
            self::RATING_WEAK,
        ];
    }

    public function getRatingArabicName(): string
    {
        return match ($this->rating) {
            self::RATING_EXCELLENT  => 'ممتاز 🌟',
            self::RATING_VERY_GOOD  => 'جيد جداً ⭐',
            self::RATING_GOOD       => 'جيد 👍',
            self::RATING_AVARAGE    => 'وسط 🙂',
            self::RATING_WEAK       => 'ضعيف ☹️',
            default                 => 'غير محدد',
        };
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class, 'enrollment_id');
    }

    public function gradeSubject()
    {
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'evaluation_user_reads', 'class_student_evaluation_id', 'user_id')
                    ->withPivot('read_at');
    }
}
