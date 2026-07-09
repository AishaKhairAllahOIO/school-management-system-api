<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialAccount extends Model
{
        protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feePlan()
    {
        return $this->belongsTo(FeePlan::class);
    }

    public function installments()
    {
        return $this->hasMany(ScheduledInstallment::class)->orderBy('installment_number');
    }

    public function transactions()
    {
        return $this->hasMany(PaymentTransaction::class)->latest();
    }
}
