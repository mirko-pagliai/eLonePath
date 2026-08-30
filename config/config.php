<?php
declare(strict_types=1);

return [
    'app' => [
        'controller_namespace' => 'App',
        'env' => env('APP_ENV', 'production'),
        'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    ],
];
