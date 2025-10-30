<?php

return [
    'base_url' => env('MAVENTA_BASE_URL', 'https://ax.maventa.com'),
    'validation_base_url' => env('MAVENTA_VALIDATION_URL', 'https://validation.maventa.com'),
    'client_id' => env('MAVENTA_CLIENT_ID'),
    'client_secret' => env('MAVENTA_CLIENT_SECRET'),
    'vendor_api_key' => env('MAVENTA_VENDOR_API_KEY'),
    'timeout' => env('MAVENTA_TIMEOUT', 10),
    'scopes' => [
        'global',
        'company',
        'user',
        'lookup',
        'invoice:send',
        'invoice:receive',
        'validate',
    ],
];
