<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $guarded = [];

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function classRooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'activity_class_room');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'activity_user')->withPivot('read_at');
    }
}
