<?php
declare(strict_types=1);

return [
    'app' => [
        'env' => env('APP_ENV', 'production'),
        'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
    ],
];
