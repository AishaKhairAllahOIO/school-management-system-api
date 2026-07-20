<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Announcement extends Model
{

    protected $guarded = [];

    public const AUDIENCE_STUDENT = 'student';
    public const AUDIENCE_STAFF   = 'staff';
    public const AUDIENCE_BOTH = 'both';

    public function scopeForAudience($query, string $role)
    {
        return $query->whereIn('audience', [$role, self::AUDIENCE_BOTH]);
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'announcement_user')->withPivot('read_at');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function classRooms()
    {
        return $this->belongsToMany(ClassRoom::class, 'announcement_class_room');
    }
}
