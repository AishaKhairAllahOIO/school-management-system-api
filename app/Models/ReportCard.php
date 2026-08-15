<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    protected $guarded = [];

    protected $casts = [
        'total_marks'     => 'decimal:2',
        'max_total_marks' => 'decimal:2',
        'failure_reasons' => 'array',
        'is_published'    => 'boolean',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function details()
    {
        return $this->hasMany(ReportCardDetail::class);
    }
}