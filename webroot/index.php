<?php
declare(strict_types=1);

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

use App\Core\Application;
use App\Core\Dispatcher;
use App\Core\ErrorHandler;
use App\Core\Router;
use App\Core\View\View;
use josegonzalez\Dotenv\Loader;

$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    new Loader($envFile)
        ->parse()
        ->toEnv();
}

$config = require dirname(__DIR__) . '/config/config.php';

$view = new View(dirname(__DIR__) . '/templates');

$errorHandler = new ErrorHandler($view, debug: $config['app']['debug']);

$app = new Application(new Router(), new Dispatcher($view), $errorHandler);

$app->run();
