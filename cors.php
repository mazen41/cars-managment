<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | This API uses JWT Bearer token authentication (not Sanctum cookie/session).
    | Therefore:
    |   - supports_credentials is FALSE  (no cookies involved)
    |   - sanctum/csrf-cookie is NOT listed (not needed for JWT auth)
    |   - X-XSRF-TOKEN is NOT required (no CSRF in a Bearer-token flow)
    |
    */

    'paths' => [
        'api/*',
        // 'sanctum/csrf-cookie' intentionally omitted: JWT Bearer auth, no cookie/CSRF flow
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:3001',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'http://dashboard.samh.test',
        'http://dashboard-dev.samh.test',
        'https://inspector.samh.store',
    ],

    'allowed_origins_patterns' => [
        '*.samh.test',
    ],

    'allowed_headers' => [
        'Accept',
        'Authorization',    // JWT Bearer token
        'Content-Type',
        'X-Requested-With',
        'X-Socket-ID',
        'System-Key',       // custom app key header
        'App-Language',     // locale header
    ],

    'exposed_headers' => [
        'Authorization',
    ],

    'max_age' => 0,

    // FALSE: JWT auth does not use cookies. Setting this to true would require
    // supports_credentials on the frontend too, and would re-introduce the need
    // for CSRF tokens. Keep false for a clean JWT-only setup.
    'supports_credentials' => false,

];
