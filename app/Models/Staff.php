<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth;

use Override;

class Staff extends Model 
{
    protected $table = 'staff';
    protected $guarded = []; 

   
    public function systemAccess()
    {
        return $this->hasOne(SystemAccess::class);
    }

   
    public function academicProfile()
    {
        return $this->hasOne(AcademicProfile::class);
    }

 
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
   
}
