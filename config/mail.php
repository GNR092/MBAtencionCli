<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),
    
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'mail.mbsignatureproperties.com'),
            'port' => env('MAIL_PORT', 465),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'username' => env('MAIL_USERNAME', 'developer2@mbsignatureproperties.com'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
        ],
    ],
    
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'developer2@mbsignatureproperties.com'),
        'name' => env('MAIL_FROM_NAME', 'MB Facturas'),
    ],
];