<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentMark extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'assessment_component_id',
        'teacher_id',
        'mark',
        'notes',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function assessmentComponent()
    {
        return $this->belongsTo(AssessmentComponent::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

       public function readers()
    {
        return $this->belongsToMany(User::class, 'mark_reads')->withPivot('read_at');
    }
}
