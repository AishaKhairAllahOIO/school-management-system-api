<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class Student extends Model
{

   protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Parent::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }



}
