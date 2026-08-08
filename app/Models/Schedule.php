<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;


class Schedule extends Model
{


protected $fillable=[

'academic_year_id',

'academic_term_id',

'status',

'score',

'generation_statistics'

];



protected $casts=[

'generation_statistics'=>'array'

];



public function entries()
{

return $this->hasMany(
ScheduleEntry::class
);

}


}
