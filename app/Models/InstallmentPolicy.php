<?php

namespace App\Models;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class InstallmentPolicy extends Model
{
        use SoftDeletes;
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(InstallmentPolicyItem::class)->orderBy('installment_number');
    }
    
}
