<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Enrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'enrollment_date' => 'date',
        'completed_at' => 'datetime',
    ];


    public function student()
    {
        return $this->belongsTo(Student::class);
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
    public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
  public function alerts()
{
    return $this->morphMany(Alert::class, 'notifiable');
}
}
