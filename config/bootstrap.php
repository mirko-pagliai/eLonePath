<?php
declare(strict_types=1);

// Define directory separator for cross-platform compatibility
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Define the root directory of the project
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

// Define config directory
if (!defined('CONFIG')) {
    define('CONFIG', ROOT . DS . 'config');
}

// Define resources directory
if (!defined('RESOURCES')) {
    define('RESOURCES', ROOT . DS . 'resources');
}

// Define templates directory
if (!defined('TEMPLATES')) {
    define('TEMPLATES', ROOT . DS . 'templates');
}

// Include Composer autoloader if exists
$composerAutoload = ROOT . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

// Load environment variables from .env file (if exists)
$envFile = CONFIG . '/.env';
if (file_exists($envFile)) {
    $dotenv = new \josegonzalez\Dotenv\Loader($envFile);
    $dotenv->parse()->toEnv();
}

// Define DEBUG constant from environment (default: false)
if (!defined('DEBUG')) {
    define('DEBUG', filter_var($_ENV['DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN));
}
