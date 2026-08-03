<?php

namespace App\Models;
use App\Enums\GradeName;

use Illuminate\Database\Eloquent\Model;

class GradeLevel extends Model
{
    protected $guarded = [];

    protected function casts(): array
        {
            return [
                'is_graduation_grade' => 'boolean',
                'level'               => 'integer',
            ];
        }
    protected $casts = [
        'name' => GradeName::class, // 👈 السحر هنا
    ];    

    public function classRooms()
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, ClassRoom::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
    public function academicStage()
    {
        return $this->belongsTo(AcademicStage::class);
    }
    public function gradeConfigurations()
    {
        return $this->hasMany(GradeConfiguration::class);
    }
}
