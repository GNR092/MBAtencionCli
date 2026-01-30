<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'file_type',
        'uuid',
        'is_valid',
        'emisor_name',
        'receptor_name',
        'related_file',
        'metadata'
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'metadata' => 'array'
    ];
}