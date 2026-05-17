<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class Role extends Model
{
    
protected $guarded = [
];
public function staffs()
{
    return $this->hasMany(Staff::class);
}

}
