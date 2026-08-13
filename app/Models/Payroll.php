<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $guarded = [];
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function contract()
    {
        return $this->belongsTo(StaffFinancialContract::class, 'contract_id');
    }
}
