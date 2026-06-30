<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherAssignment extends Model
{
        protected $guarded = [];


        public function staff(){
            return $this->belongsTo(Staff::class);
        }

        public function alerts(){
            return $this->hasMany(Alert::class);
        }
}
