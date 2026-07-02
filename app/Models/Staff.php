<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth;

use Override;

class Staff extends Model
{
    protected $guarded = [];

   public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function alerts()
{
    return $this->morphMany(Alert::class, 'notifiable');
}

}
