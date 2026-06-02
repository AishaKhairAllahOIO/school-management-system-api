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
const GUARDIAN = 'GUARDIAN';
const SERVICES_STAFF = 'SERVICES_STAFF';

protected $guarded = [
];
public function users()
{
    return $this->hasMany(User::class);
}

}
