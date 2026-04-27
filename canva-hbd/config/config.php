<?php

return [
    'admin_role' => env('HBD_ADMIN_ROLE', 'administrador'),

    'user_class' => env('HBD_USER_CLASS', 'App\\Models\\User'),

    'birthday_field' => env('HBD_BIRTHDAY_FIELD', 'fecha_nacimiento'),

    'send_hour' => env('HBD_SEND_HOUR', '09:00'),

    'auto_send' => env('HBD_AUTO_SEND', true),

    'send_days_before' => env('HBD_SEND_DAYS_BEFORE', 0),

    'template_default' => 'feliz-cumpleanos',

    'assets_path' => 'modules/canva-hbd/src/Assets',

    'uploads_path' => 'hbd-uploads',
];
