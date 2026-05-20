<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class Role extends Model
{

const STUDENT = 'STUDENT';
const TEACHER = 'TEACHER';
const COUNSELOR = 'COUNSELOR';
const SECRETARY = 'SECRETARY';
const ADVISOR = 'ADVISOR';
const SUPER_ADMIN = 'SUPER_ADMIN';
const PARENT = 'PARENT';
    
protected $guarded = [
];
public function staffs()
{
    return $this->hasMany(Staff::class);
}

}
