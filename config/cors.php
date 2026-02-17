<?php

return [
    'paths' => ['api/*', 'auth/*', 'sanctum/csrf-cookie', '*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://mozpesquisa.netlify.app',
        'http://localhost:9000',
        'http://localhost:9001',
        'http://localhost:8080',
        'http://localhost:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
