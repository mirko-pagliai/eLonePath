<?php
declare(strict_types=1);

/**
 * Entry point.
 *
 * It builds the necessary objects, then the application, and launches `Application::run()`.
 */

use Elone\Core\Application;
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

require dirname(__DIR__) . '/config/bootstrap.php';

$envFile = ROOT . '/.env';
if (file_exists($envFile)) {
    new Loader($envFile)
        ->parse()
        ->toEnv();
}

$router = new Router();
$dispatcher = new Dispatcher();
$errorHandler = new ErrorHandler(debug: CONFIG['app']['debug']);

$app = new Application($router, $dispatcher, $errorHandler);
$app->run();
