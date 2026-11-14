<?php

return [
    'name' => env('APP_NAME', 'WatchKru'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Tehran'),
    'locale' => env('APP_LOCALE', 'fa'),
    'fallback_locale' => 'en',
    'faker_locale' => 'fa_IR',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
    ],
];
