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

$view = new View(dirname(__DIR__) . '/templates');

$app = new Application(
    new Router(),
    new Dispatcher($view),
    new ErrorHandler($view, debug: true),
);

$app->run();
