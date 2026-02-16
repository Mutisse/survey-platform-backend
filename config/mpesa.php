<?php
// config/mpesa.php

return [
    /*
    |--------------------------------------------------------------------------
    | Configurações M-Pesa
    |--------------------------------------------------------------------------
    */

    'api_url' => env('MPESA_API_URL', 'https://api.vm.co.mz'),

    'api_key' => env('MPESA_API_KEY'),

    'public_key' => env('MPESA_PUBLIC_KEY'),

    'service_provider_code' => env('MPESA_SERVICE_PROVIDER_CODE'),

    'initiator_identifier' => env('MPESA_INITIATOR_IDENTIFIER'),

    'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),

    /*
    |--------------------------------------------------------------------------
    | IPs Confiáveis para Webhook
    |--------------------------------------------------------------------------
    | Lista de IPs ou ranges de IP da M-Pesa que podem enviar webhooks
    */
    'trusted_ips' => [
        '196.216.64.0/24',  // M-Pesa Production
        '197.218.0.0/16',   // M-Pesa Production
        '105.21.0.0/16',    // M-Pesa Production
        '41.220.0.0/16',    // M-Pesa Production
        '::1',               // localhost IPv6 (para testes)
        '127.0.0.1',         // localhost IPv4 (para testes)
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    */
    'timeout' => 30,

    'connect_timeout' => 10,
];
