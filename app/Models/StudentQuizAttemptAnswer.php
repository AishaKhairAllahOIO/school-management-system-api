<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentQuizAttemptAnswer extends Model
{
    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption()
    {
        return $this->belongsTo(Option::class, 'selected_option_id');
    }
}
