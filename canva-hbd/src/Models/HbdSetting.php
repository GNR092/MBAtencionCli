<?php

namespace Canva\HBD\Models;

use Illuminate\Database\Eloquent\Model;

class HbdSetting extends Model
{
    protected $table = 'hbd_settings';

    protected $fillable = [
        'auto_send',
        'send_days_before',
        'send_hour',
        'subject_template',
        'is_active',
    ];

    protected $casts = [
        'auto_send' => 'boolean',
        'send_days_before' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'auto_send' => true,
        'send_days_before' => 0,
        'send_hour' => '09:00',
        'subject_template' => '¡Feliz cumpleaños, {nombre}!',
        'is_active' => true,
    ];

    protected static function booted(): void
    {
        static::created(function (HbdSetting $setting) {
            if (static::count() > 1) {
                $setting->delete();
            }
        });
    }

    public static function getSettings(): self
    {
        return static::first() ?? static::create([]);
    }

    public static function getOrCreate(): self
    {
        return static::first() ?? static::create([
            'auto_send' => true,
            'send_days_before' => 0,
            'send_hour' => '09:00',
            'subject_template' => '¡Feliz cumpleaños, {nombre}!',
            'is_active' => true,
        ]);
    }
}
