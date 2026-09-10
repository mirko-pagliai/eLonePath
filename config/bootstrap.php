<?php
declare(strict_types=1);

use josegonzalez\Dotenv\Loader;

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

if (!defined('APP')) {
    define('APP', require ROOT . '/config/config.php');
}

const LOCALES = ROOT . '/resources/locales';

if (!defined('STORIES')) {
    define('STORIES', ROOT . '/resources/stories');
}

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

require ROOT . '/packages/Core/config/bootstrap.php';
