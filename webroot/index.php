<?php
declare(strict_types=1);

/**
 * Entry point.
 *
 * It builds the necessary objects, then the application, and launches `Application::run()`.
 */

use App\Core\Application;
use App\Core\Dispatcher;
use App\Core\ErrorHandler;
use App\Core\Router;
use App\Core\View\View;
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

require dirname(__DIR__) . '/vendor/autoload.php';

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    new Loader($envFile)
        ->parse()
        ->toEnv();
}

$config = require dirname(__DIR__) . '/config/config.php';

$router = new Router();

$view = new View(dirname(__DIR__) . '/templates', $router);

$errorHandler = new ErrorHandler($view, debug: $config['app']['debug']);

$app = new Application($router, new Dispatcher($view), $errorHandler);

$app->run();
