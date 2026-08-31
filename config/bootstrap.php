<?php
declare(strict_types=1);

use josegonzalez\Dotenv\Loader;

define('ROOT', dirname(__DIR__));

require ROOT . '/packages/Core/config/bootstrap.php';

/**
 * It is only used if the `/.env` file is present and the `josegonzalez/dotenv` package is installed.
 *
 * This package should not be installed in production.
 */
$envFile = ROOT . '/.env';
if (file_exists($envFile) && class_exists(Loader::class)) {
    new Loader($envFile)
        ->parse()
        ->toEnv();
}
