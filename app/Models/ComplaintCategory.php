<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplaintCategory extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function types(): HasMany
    {
        return $this->hasMany(ComplaintType::class);
    }
}