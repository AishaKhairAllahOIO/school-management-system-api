<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentPolicyItem extends Model
{
        protected $guarded = [];

    public function policy()
    {
        return $this->belongsTo(InstallmentPolicy::class, 'installment_policy_id');
    }
}
