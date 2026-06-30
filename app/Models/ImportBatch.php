<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ImportBatch extends Model
{
    protected $fillable = [
        'batch_title', 'status', 'total_rows', 'processed_rows', 'successful_rows', 'failed_rows', 'file_path', 'imported_by_user_id',
    ];

    public function errors()
    {
        return $this->hasMany(ImportError::class, 'import_batch_id');
    }

    public function importer()
    {
        return $this->belongsTo(User::class, 'imported_by_user_id');
    }}
