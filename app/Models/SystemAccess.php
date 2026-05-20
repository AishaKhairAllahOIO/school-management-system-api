<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable; // مهم جداً
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class SystemAccess extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $table = 'system_accesses'; // تحديد اسم الجدول

    protected $guarded = []; 

    protected $hidden = [
        'password',
    ];

    // لعمل Hash تلقائي لكلمة المرور عند إنشائها (Laravel 10+)
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
