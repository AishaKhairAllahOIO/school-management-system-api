<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemAccess extends Model
{
    protected $guarded = [];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
