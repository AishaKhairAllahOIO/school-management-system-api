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
}
