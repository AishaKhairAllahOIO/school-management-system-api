<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'practice_quiz_id',
        'question_text',
        'mark',
    ];

    protected $casts = [
        'mark' => 'float',
    ];

    public function practiceQuiz(): BelongsTo
    {
        return $this->belongsTo(PracticeQuiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }
}
