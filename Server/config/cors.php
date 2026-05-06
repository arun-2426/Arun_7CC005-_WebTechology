<?php

/*
|--------------------------------------------------------------------------
| CORS — only the SPA origin is allowed.
|--------------------------------------------------------------------------
|
| FRONTEND_URL is read from .env. Locally it's http://localhost:5283;
| on mi-linux it'll be wherever the build is hosted. Splitting it out
| here means we never hard-code an origin in code.
|
*/

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Locally, the browser may load the SPA at either http://localhost:5283
    // or http://127.0.0.1:5283 — they're different origins to CORS even
    // though they resolve identically. Accept both so dev doesn't break
    // depending on how you typed the URL. In production only FRONTEND_URL
    // matters (the alternate-host swap below is a no-op for real domains).
    'allowed_origins' => array_values(array_unique(array_filter([
        env('FRONTEND_URL'),
        env('FRONTEND_URL') ? str_replace('localhost', '127.0.0.1', env('FRONTEND_URL')) : null,
        env('FRONTEND_URL') ? str_replace('127.0.0.1', 'localhost', env('FRONTEND_URL')) : null,
    ]))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,
];
