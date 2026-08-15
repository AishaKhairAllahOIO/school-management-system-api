<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardDetail extends Model
{
    protected $guarded = [];

    protected $casts = [
        'evaluations_summary' => 'array',
        'subject_total'       => 'decimal:2',
        'passing_mark'        => 'decimal:2',
        'max_mark'            => 'decimal:2',
        'is_failing_subject'  => 'boolean',
    ];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class);
    }

    public function gradeSubject()
    {
        return $this->belongsTo(GradeSubject::class);
    }
}