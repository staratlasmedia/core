<?php

$allowedOrigins = array_values(array_filter(array_map(
    'trim',
    explode(',', env('CORE_ALLOWED_ORIGINS', 'https://www.clubalfa.it,https://www.motorisumotori.it')),
)));

return [
    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Content-Type',
        'X-Core-Client',
        'X-Core-Signature',
        'X-Requested-With',
    ],

    'exposed_headers' => [],

    'max_age' => 600,

    'supports_credentials' => true,
];
