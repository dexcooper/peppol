<?php

return [
    'base_url' => env('MAVENTA_BASE_URL', 'https://ax.maventa.com'),
    'client_id' => env('MAVENTA_CLIENT_ID'),
    'client_secret' => env('MAVENTA_CLIENT_SECRET'),
    'vendor_api_key' => env('MAVENTA_VENDOR_API_KEY'),
    'timeout' => env('MAVENTA_TIMEOUT', 10),
    'scopes' => [
        'company:lookup',
        'invoice:send',
        'invoice:receive',
    ],
];
