<?php
declare(strict_types=1);

/**
 * eLonePath - Front Controller.
 *
 * Entry point for the application. Sets up HttpKernel and handles requests.
 *
 * Run on the dev server with:
 *
 * ```
 * composer run-server
 * ```
 */

require_once __DIR__ . '/../config/bootstrap.php';

use eLonePath\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

// Create request from globals
$request = Request::createFromGlobals();

// Initialize session
$session = new Session(new NativeSessionStorage([
    // By default, the session is maintained until the browser is closed.
    'cookie_lifetime' => 0,
    'cookie_httponly' => true,
    'cookie_samesite' => 'lax',
]));
$session->start();
$request->setSession($session);

/**
 * Loads routes.
 *
 * @link config/routes.php
 */
$routes = require_once CONFIG . '/routes.php';

// Set up routing context and matcher
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

try {
    $request->attributes->add($matcher->match($request->getPathInfo()));

    $controllerResolver = new ControllerResolver();
    $argumentResolver = new ArgumentResolver();

    $controller = $controllerResolver->getController($request);
    if (!$controller) {
        throw new ResourceNotFoundException('Controller not found for the request');
    }

    assert(is_array($controller));
    assert($controller[0] instanceof Controller);
    assert(is_string($controller[1]));

    $controller[0]->view->setRequest($request);
    $arguments = $argumentResolver->getArguments($request, $controller);

    $response = call_user_func_array($controller, $arguments);

    /**
     * If the controller method returns `void` instead of a `Response` instance, `$response` here will be `null`.
     *
     * In this case, call the `Controller::render()` method.
     */
    if (!$response instanceof Response) {
        $response = $controller[0]->render();
    }

} catch (ResourceNotFoundException $e) {
    $response = new Response('Page not found', 404);
} catch (\Throwable $e) {
    $response = new Response('Error: ' . $e->getMessage(), 500);
}

$response->send();