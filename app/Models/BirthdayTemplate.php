<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BirthdayTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'background_path',
        'zones_json',
        'overlay_images',
        'default_message',
        'is_active',
        'canvas_width',
        'canvas_height',
    ];

    protected $casts = [
        'zones_json' => 'array',
        'overlay_images' => 'array',
        'is_active' => 'boolean',
    ];
}