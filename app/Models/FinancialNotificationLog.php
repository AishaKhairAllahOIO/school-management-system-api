<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialNotificationLog extends Model
{
    protected $guarded = [];
    protected $casts = [
        'sent_at' => 'datetime',
    ];
    public function account()
    {
        return $this->belongsTo(FinancialAccount::class, 'financial_account_id');
    }
    public function scheduledInstallment()
    {
        return $this->belongsTo(ScheduledInstallment::class, 'scheduled_installment_id');
    }
}
