<?php

return [
    'admin_path' => env('CORE_ADMIN_PATH', 'core-admin'),

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('CORE_ALLOWED_ORIGINS', 'https://www.clubalfa.it,https://www.motorisumotori.it')),
    ))),

    'sites' => [
        'clubalfa_it' => [
            'origin' => 'https://www.clubalfa.it',
            'language' => 'it',
            'push_group' => 'clubalfa_it',
            'manifest_id' => '/pwa/clubalfa-it',
            'service_worker_url' => '/smart_sw.js',
            'service_worker_scope' => '/',
        ],
        'clubalfa_en' => [
            'origin' => 'https://www.clubalfa.it',
            'language' => 'en',
            'push_group' => 'clubalfa_en',
            'manifest_id' => '/pwa/clubalfa-en',
            'service_worker_url' => '/en/smart_sw.js',
            'service_worker_scope' => '/en/',
        ],
        'motorisumotori_it' => [
            'origin' => 'https://www.motorisumotori.it',
            'language' => 'it',
            'push_group' => 'motorisumotori_it',
            'manifest_id' => null,
            'service_worker_url' => '/smart_sw.js',
            'service_worker_scope' => '/',
        ],
    ],
];
