<?php
declare(strict_types=1);

/**
 * eLonePath - Front Controller. Entry point for the application. Sets up HttpKernel and handles requests.
 *
 * Run on the dev server with:
 *
 * ```
 * $ php -d opcache.jit=disable -S localhost:8000
 * ```
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

// Create request from globals
$request = Request::createFromGlobals();

// Initialize session
$session = new Session(new NativeSessionStorage([
    'cookie_lifetime' => 3600,
    'cookie_httponly' => true,
    'cookie_samesite' => 'lax',
]));
$session->start();
$request->setSession($session);

// Load routes configuration
$routes = require __DIR__ . '/../config/routes.php';

// Setup routing context and matcher
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

// Setup event dispatcher
$dispatcher = new EventDispatcher();

// Add routing listener
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

// Setup controller and argument resolvers
$controllerResolver = new ControllerResolver();
$argumentResolver = new ArgumentResolver();

// Create HttpKernel
$kernel = new HttpKernel(
    $dispatcher,
    $controllerResolver,
    new RequestStack(),
    $argumentResolver,
);

// Handle request and send response
try {
    $response = $kernel->handle($request);
    $response->send();
    $kernel->terminate($request, $response);
}
catch (\Exception $e) {
    // Basic error handling - in production you'd want better error pages
    $response = new Response(
        'An error occurred: ' . $e->getMessage(),
        Response::HTTP_INTERNAL_SERVER_ERROR,
    );
    $response->send();
}