<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyMaterial extends Model
{
    protected $guarded = [];

    public function gradeSubject()
    {
        return $this->belongsTo(GradeSubject::class);
    }

    public function gradeLevel(){
        return $this->belongsTo(GradeLevel::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'material_user_reads', 'study_material_id', 'user_id')
                    ->withPivot('read_at');
    }
}
