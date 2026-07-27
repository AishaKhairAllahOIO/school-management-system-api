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

use Illuminate\Database\Eloquent\SoftDeletes; // 👈 1. الاستدعاء الصحيح

class User extends Authenticatable
{


    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];
    protected $guard_name = 'sanctum';



    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [

            'password' => 'hashed',
        ];
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? (filter_var($value, FILTER_VALIDATE_URL) ? $value : asset('storage/' . $value)) : null
        );
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

}
