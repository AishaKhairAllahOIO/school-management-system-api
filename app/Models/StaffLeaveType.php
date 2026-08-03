<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLeaveType extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'max_days_per_academic_year' => 'integer',
    ];

    public function leaves()
    {
        return $this->hasMany(StaffLeave::class, 'leave_type_id');
    }
}
