<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Alert extends Model
{
    protected $guarded = [];

    protected $casts = [
        'meta'    => 'array',
        'is_read' => 'boolean',
    ];

    public const TYPE_ABSENCE  = 'absence';
    public const TYPE_BEHAVIOR = 'behavior';
    public const TYPE_LATE     = 'late';
    public const TYPE_ESCAPE   = 'escape';
    public const TYPE_PAYMENT  = 'payment';
    public const TYPE_SALARY = 'salary';
    public const TYPE_HOMEWORK = 'homework';
    public const TYPE_PAYED = 'payed';
    public const TYPE_EXPULSION = 'expulsion';
    public const AUDIENCE_STUDENT = 'student';
    public const AUDIENCE_STAFF   = 'staff';
    public const TYPE_WARNING = 'warning';
    public const TYPE_SYSTEM_NOTICE = 'system_notice';
    public const TYPE_COMPLAIN = 'complain';
    public const TYPE_REPORT_CARD = 'report_card';


    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForAudience($query, string $audience)
    {
        return $query->where('audience', $audience);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function readers()
    {
        return $this->belongsToMany(User::class, 'alert_reads')->withPivot('read_at');
    }
}
