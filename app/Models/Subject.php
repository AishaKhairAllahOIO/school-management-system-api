<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{

    protected $guarded = [];

    public function gradeSubjects()
    {
        return $this->hasMany(GradeSubject::class);
    }

    public function scheduleEntries()
    {
        return $this->hasManyThrough(
            ScheduleEntry::class,
            GradeSubject::class
        );
    }


    protected static function booted()
    {
        static::deleting(function ($subject) {

            if ($subject->scheduleEntries()->exists()) {

                throw new \Exception(
                    'Cannot delete this subject because it is used in the schedule.'
                );

            }

        });
    }

}
