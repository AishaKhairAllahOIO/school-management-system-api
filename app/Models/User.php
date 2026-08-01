<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Casts\Attribute;

use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{


    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;


    protected $guarded = [];
    protected $guard_name = 'sanctum';




    protected function casts(): array
    {
        return [

            'password' => 'hashed',
        ];
    }

    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function guardian()
    {
        return $this->hasOne(Guardian::class);
    }

    public function staff()
    {
        return $this->hasOne(Staff::class);
    }
    public function serviceStaff()
    {
        return $this->hasOne(ServiceStaff::class);
    }

    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function readAlerts()
    {
        return $this->belongsToMany(Alert::class, 'alert_reads')->withPivot('read_at');
    }

    public function readAnnouncements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')->withPivot('read_at');
    }
    public function readActivities()
    {
        return $this->belongsToMany(Activity::class, 'activity_user')->withPivot('read_at');
    }

    public function readHomeworks()
    {
        return $this->belongsToMany(Homework::class, 'homework_user_reads', 'user_id', 'homework_id')
            ->withPivot('read_at');
    }

    public function readEvaluations()
    {
        return $this->belongsToMany(ClassStudentEvaluation::class, 'evaluation_user_reads', 'user_id', 'class_student_evaluation_id')
            ->withPivot('read_at');
    }
    public function readMarks()
    {
        return $this->belongsToMany(StudentMark::class, 'mark_user_reads', 'user_id', 'student_mark_id')
            ->withPivot('read_at');
    }

    public function readPracticeQuizzes()
    {
        return $this->belongsToMany(PracticeQuiz::class, 'practice_quiz_user_reads', 'user_id', 'practice_quiz_id')
            ->withPivot('read_at');
    }

}
