<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePlanExtraService extends Model
{
        protected $guarded = [];

    public function feePlan()
    {
        return $this->belongsTo(FeePlan::class);
    }
}
