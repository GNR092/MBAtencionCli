<?php

namespace Canva\HBD\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HbdTemplate extends Model
{
    protected $table = 'hbd_templates';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'thumbnail',
        'is_active',
    ];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];

    public function sents(): HasMany
    {
        return $this->hasMany(HbdSent::class, 'hbd_template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getActive()
    {
        return static::active()->first();
    }

    public static function getDefaultContent(): array
    {
        return [
            'desktop' => [
                [
                    'name' => 'Hero',
                    'height' => 500,
                    'bgType' => 'color',
                    'bgValue' => '#1a1a2e',
                    'components' => [
                        [
                            'type' => 'text',
                            'subtype' => 'heading',
                            'top' => 30,
                            'left' => 10,
                            'width' => 80,
                            'content' => '¡Feliz cumpleaños, {nombre}!',
                            'color' => '#ffffff',
                            'fontSize' => 48,
                            'fontWeight' => 'bold',
                            'textAlign' => 'center',
                        ],
                        [
                            'type' => 'text',
                            'subtype' => 'body',
                            'top' => 55,
                            'left' => 15,
                            'width' => 70,
                            'content' => 'Te deseamos un día lleno de alegría y bendiciones.',
                            'color' => '#d8c495',
                            'fontSize' => 20,
                            'textAlign' => 'center',
                        ],
                        [
                            'type' => 'image',
                            'top' => 10,
                            'left' => 40,
                            'width' => 20,
                            'height' => 15,
                            'url' => '/img/hbd-cake.png',
                        ],
                    ],
                ],
            ],
            'tablet' => [],
            'mobile' => [],
        ];
    }
}
