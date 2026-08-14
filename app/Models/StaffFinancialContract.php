<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffFinancialContract extends Model
{
    protected $guarded = [];
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'contract_id');
    }
}
