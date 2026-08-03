<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth;

use Override;

class Staff extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function alerts()
    {
        return $this->morphMany(Alert::class, 'notifiable');
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class, 'teacher_id');
    }

    public function teacherWorkloads()
    {
        return $this->hasMany(TeacherWorkload::class, 'teacher_id');
    }
    public function leaves()
    {
        return $this->hasMany(StaffLeave::class);
    }

    public function attendances()
    {
        return $this->hasMany(StaffAttendance::class);
    }
}
