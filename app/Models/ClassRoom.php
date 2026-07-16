<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassRoom extends Model
{
    protected $guarded = [];
    protected $appends = ['current_students_count', 'available_seats'];
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
    public function getCurrentStudentsCountAttribute()
    {
        return $this->enrollments()->where('enrollment_status', 'enrolled')->count();
    }
    public function getAvailableSeatsAttribute()
    {
        return max(0, $this->capacity - $this->current_students_count);
    }
    public function teacherAssignments(){
        return $this->hasMany(TeacherAssignment::class);
    }

    
}
