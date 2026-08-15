<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplaintType extends Model
{
    protected $fillable = [
        'complaint_category_id', 
        'title', 
        'severity', 
        'is_active'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ComplaintCategory::class, 'complaint_category_id');
    }
}