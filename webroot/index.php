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
use App\Core\Routing\Router;
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
require dirname(__DIR__) . '/config/bootstrap.php';

$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    new Loader($envFile)
        ->parse()
        ->toEnv();
}

$config = require ROOT . '/config/config.php';

$router = new Router();

$view = new View(ROOT . '/templates');

$errorHandler = new ErrorHandler($view, debug: $config['app']['debug']);

$app = new Application($router, new Dispatcher($view), $errorHandler);

$app->run();
