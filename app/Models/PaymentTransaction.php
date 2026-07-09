<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
        protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }
}
