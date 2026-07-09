<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledInstallment extends Model
{
        protected $guarded = [];
    protected $casts = [
        'due_date' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }
}
