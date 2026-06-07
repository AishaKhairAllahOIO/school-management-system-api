<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $guarded = [];

    protected $casts = [
        'working_days' => 'array',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function images() {
        return $this->hasMany(SchoolImage::class);
    }
    public function academicSetting() {
        return $this->hasOne(AcademicSetting::class, 'school_id');
    }
}
