<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $fillable = ['name'];


    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
}
