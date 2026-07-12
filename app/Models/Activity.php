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

      public function classRoom()
    {
        return $this->belongsTo(ClassRoom::class);
    }

  public function readers()
    {
        return $this->belongsToMany(User::class, 'activity_user')->withPivot('read_at');
    }
}
