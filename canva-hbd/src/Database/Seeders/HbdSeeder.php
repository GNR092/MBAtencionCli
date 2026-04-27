<?php

namespace Canva\HBD\Database\Seeders;

use Canva\HBD\Models\HbdSetting;
use Canva\HBD\Models\HbdTemplate;
use Illuminate\Database\Seeder;

class HbdSeeder extends Seeder
{
    public function run(): void
    {
        HbdSetting::firstOrCreate([
            'auto_send' => true,
            'send_days_before' => 0,
            'send_hour' => '09:00',
            'subject_template' => '¡Feliz cumpleaños, {nombre}!',
            'is_active' => true,
        ]);

        HbdTemplate::firstOrCreate([
            'slug' => 'feliz-cumpleanos',
        ], [
            'name' => 'Feliz Cumpleaños',
            'slug' => 'feliz-cumpleanos',
            'content' => HbdTemplate::getDefaultContent(),
            'is_active' => true,
        ]);
    }
}
