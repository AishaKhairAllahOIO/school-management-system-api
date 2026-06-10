<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeScale extends Model
{
    protected $guarded = [];
    public function academicSetting() {
    return $this->belongsTo(AcademicSetting::class, 'setting_id');
}
}
