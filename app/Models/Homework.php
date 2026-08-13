<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Homework extends Model
{
    protected $table = 'homeworks';

    protected $guarded = [];


    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'teacher_id');
    }


    public function gradeSubject(): BelongsTo
    {
        return $this->belongsTo(GradeSubject::class, 'grade_subject_id');
    }


    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id');
    }


    public function classRooms(): BelongsToMany
    {
        return $this->belongsToMany(
            ClassRoom::class,
            'class_room_homework',
            'homework_id',
            'class_room_id'
        )->withTimestamps();
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'homework_user_reads', 'homework_id', 'user_id')
            ->withPivot('read_at');
    }
}
