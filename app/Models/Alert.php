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

    // أنواع التنبيهات
    public const TYPE_ABSENCE  = 'absence';
    public const TYPE_BEHAVIOR = 'behavior';
    public const TYPE_LATE     = 'late';
    public const TYPE_ESCAPE   = 'escape';
    public const TYPE_PAYMENT  = 'payment';
    public const TYPE_SALARY = 'salary';
    public const TYPE_HOMEWORK = 'homework';
    public const TYPE_PAYED = 'payed';

    // الجمهور
    public const AUDIENCE_STUDENT = 'student';
    public const AUDIENCE_STAFF   = 'staff';

    /**
     * العلاقة متعددة الأشكال: قد يكون Enrollment أو Staff.
     */
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
