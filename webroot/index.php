<?php
declare(strict_types=1);

/**
 * Entry point.
 *
 * It builds the necessary objects, then the application, and launches `Application::run()`.
 */

use Elone\Core\Application;
use Elone\Core\Configuration;
use Elone\Core\Dispatcher;
use Elone\Core\ErrorHandler;
use Elone\Core\Routing\Router;
use josegonzalez\Dotenv\Loader;

if (PHP_SAPI === 'cli-server') {
    $url = $_SERVER['REQUEST_URI'] ?? '/';
    assert(is_string($url));
    $path = parse_url($url, PHP_URL_PATH);
    $file = __DIR__ . $path;

    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

$envFile = ROOT . '/.env';
if (file_exists($envFile) && class_exists(Loader::class)) {
    new Loader($envFile)
        ->parse()
        ->toEnv();
}

$appConfig = require ROOT . '/config/config.php';

$configuration = new Configuration(
    rootPath: ROOT,
    namespace: $appConfig['app']['namespace'],
    debug: $appConfig['app']['debug'],
);

$router = new Router($configuration);
$dispatcher = new Dispatcher($configuration);
$errorHandler = new ErrorHandler($configuration);

$app = new Application($router, $dispatcher, $errorHandler);
$app->run();
