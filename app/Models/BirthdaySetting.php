<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdaySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'send_time',
        'timezone',
        'max_attempts',
        'retry_minutes',
    ];
}
