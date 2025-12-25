<?php
declare(strict_types=1);

/**
 * eLonePath - Front Controller. Entry point for the application. Sets up HttpKernel and handles requests.
 *
 * Run on the dev server with:
 *
 * ```
 * $ php -d opcache.jit=disable -S localhost:8000 -t public
 * ```
 */

require_once __DIR__ . '/../config/bootstrap.php';

use eLonePath\ErrorHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\HttpKernel;
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
 * Loads routes configuration.
 *
 * @link config/routes.php
 */
$routes = require_once CONFIG . '/routes.php';

// Set up routing context and matcher
$context = new RequestContext();
$context->fromRequest($request);
$matcher = new UrlMatcher($routes, $context);

// Setup event dispatcher
$dispatcher = new EventDispatcher();

// Add routing listener
$dispatcher->addSubscriber(new RouterListener($matcher, new RequestStack()));

// Set up controller and argument resolvers
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
} catch (HttpExceptionInterface $e) {
    // Get HTTP status code from exception
    $statusCode = $e->getStatusCode();

    // Map status code to message
    $statusMessages = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        408 => 'Request Timeout',
        429 => 'Too Many Requests',
    ];
    $statusMessage = $statusMessages[$statusCode] ?? 'Client Error';

    // Log to terminal
    error_log(sprintf(
        "\n\n=== HTTP %d ===\nPath: %s\nMessage: %s\n",
        $statusCode,
        $request->getPathInfo(),
        $e->getMessage(),
    ));

    // Send error response to browser
    http_response_code($statusCode);

    require TEMPLATES . '/errors/400.php';
} catch (Throwable $e) {
    // Log to terminal
    error_log(sprintf(
        "\n\n=== EXCEPTION ===\nType: %s\nMessage: %s\nFile: %s:%d\n\nStack Trace:\n%s\n",
        get_class($e),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine(),
        $e->getTraceAsString(),
    ));

    // Send error response to browser
    ErrorHandler::display($e);
}
