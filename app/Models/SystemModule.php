<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemModule extends Model
{
    protected $fillable = ['name'];

    /**
     * العلاقة الوظيفية: الوحدة الواحدة تمتلك عدة صلاحيات
     */
    public function permissions()
    {
        return $this->hasMany(Permission::class, 'module_id');
    }
}
