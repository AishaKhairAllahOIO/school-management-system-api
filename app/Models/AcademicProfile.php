<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicProfile extends Model
{
    protected $guarded = [];
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
