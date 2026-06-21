<?php

namespace App\Models;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    // نخبر لارافيل أن يسمح بإدخال هذه الحقول الإضافية
    protected $fillable = [
        'name', 
        'guard_name', 
        'module_id', 
        'access_level'
    ];

    /**
     * العلاقة العكسية: الصلاحية تنتمي لوحدة (موديول) واحد
     */
    public function module()
    {
        return $this->belongsTo(SystemModule::class, 'module_id');
    }
}
