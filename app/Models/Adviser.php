<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Adviser extends Model
{
    protected $guarded = [];
    public function staff()
    {
        return $this->belongsTo('staff');
    }
     public function gradeConfigurations()
    {
        return $this->hasMany(GradeConfiguration::class);
    }
}
