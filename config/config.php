<?php
declare(strict_types=1);

/**
 * Application configuration, loaded once by `config/bootstrap.php` into the `APP` constant.
 *
 * Every value here can be overridden without touching this file: `env($key, $default)` (see
 * `packages/Core/src/global_functions.php`) reads the real process environment first, falling back to the given
 * default only if it's unset — and in development, `config/bootstrap.php` loads a `/.env` file into the process
 * environment before this file runs, so a value in `.env` always wins over the default written here.
 *
 * Only values that can legitimately change per environment or deployment belong in this file. A fixed fact about
 * the codebase — like the app's controller namespace — doesn't: it's either already covered by a sensible default
 * in the Core itself or, if it genuinely needs overriding, set as an explicit `define()` in `config/bootstrap.php`,
 * before the Core's own bootstrap runs.
 */
return [
    // The runtime environment: 'production', 'development', 'testing', etc. Nothing reads this yet, but it's the
    // natural switch for anything that should behave differently per environment later on.
    'env' => env('APP_ENV', 'production'),

    // Whether error pages show the exception's own message, class, and trace. Never leave this `true` in
    // production.
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOL),
];
