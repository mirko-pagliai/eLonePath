<?php
declare(strict_types=1);

use eLonePath\Controller\HomeController;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

$routes = new RouteCollection();

// Home route
$routes->add(name: 'home', route: new Route('/', [
    '_controller' => [HomeController::class, 'index'],
]));

return $routes;
