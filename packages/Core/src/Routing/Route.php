<?php
declare(strict_types=1);

namespace Elone\Core\Routing;

use Elone\Core\Controller;
use Elone\Core\Exception\ActionNotFoundException;
use Elone\Core\Exception\ControllerNotFoundException;
use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\Utility\WordCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * Represents a route in the application, mapping a controller and action to a specific URL path.
 */
readonly class Route
{
    /**
     * Constructor method for initializing the routing to a specific controller and action.
     *
     * Validates that the provided controller name and action exist, ensures the controller class extends the base
     * `Controller` class, and checks that the specified action is accessible.
     *
     * @param string $controller The controller identifier, already in PascalCase (e.g. `Pages`, `UsersSettings`).
     *  Callers deriving it from raw, untrusted input — a URL segment — must normalize it (see `WordCase`) before
     *  constructing a `Route`.
     * @param string $action The name of the action method within the controller to be executed.
     * @param list<string> $params Optional parameters to be passed to the action method.
     *
     * @return void
     *
     * @throws \Elone\Core\Exception\ControllerNotFoundException If the controller name is invalid, the specified
     * controller class is not found, or the class is abstract (e.g. the app's own `AppController`) and so can
     * never actually be dispatched to.
     * @throws \Elone\Core\Exception\RouteNotFoundException If the controller class does not extend the base
     * `Controller` class.
     * @throws \Elone\Core\Exception\ActionNotFoundException If the specified action is not found in the controller
     * class or is not public.
     */
    public function __construct(public string $controller, public string $action, public array $params = [])
    {
        if (!ctype_alpha($controller) || !ctype_upper($controller[0])) {
            throw new ControllerNotFoundException("Invalid controller name: `$controller`.");
        }

        $controllerClass = $this->controllerClass();
        if (!class_exists($controllerClass)) {
            throw new ControllerNotFoundException("Controller not found: `$controllerClass`.");
        }

        if (!is_subclass_of($controllerClass, Controller::class)) {
            throw new RouteNotFoundException("`$controllerClass` must extend `" . Controller::class . '`.');
        }

        // An abstract class (the app's own AppController, or any other abstract base) is never a real, dispatchable
        // controller — treated the same as "not found" rather than a distinct case, so a probing request can't tell
        // the two apart.
        if (new ReflectionClass($controllerClass)->isAbstract()) {
            throw new ControllerNotFoundException("Controller not found: `$controllerClass`.");
        }

        if (!method_exists($controllerClass, $action)) {
            throw new ActionNotFoundException("Action not found: `$controllerClass::$action()`.");
        }

        $method = new ReflectionMethod($controllerClass, $action);
        if (!$method->isPublic()) {
            throw new ActionNotFoundException("Action is not public: `$controllerClass::$action()`.");
        }
    }

    /**
     * Generates the fully qualified class name of the controller based on the provided controller identifier.
     *
     * @return class-string<\Elone\Core\Controller> The generated fully qualified class name for the controller.
     */
    public function controllerClass(): string
    {
        /** @var class-string<\Elone\Core\Controller> $className */
        $className = APP_NAMESPACE . "\\Controller\\{$this->controller}Controller";

        return $className;
    }

    /**
     * Generates a URL path by combining the controller name, action, and additional parameters.
     *
     * The controller name is converted to a kebab-case, while all segments are URL-encoded to ensure safe inclusion in
     * the URL. The segments are then concatenated using slashes to form the complete path.
     *
     * @return string The generated URL path, starting with a forward slash (`/`).
     */
    public function path(): string
    {
        $segments = [
            new WordCase($this->controller)->kebabCase(),
            $this->action,
            ...$this->params,
        ];

        return '/' . implode('/', array_map(
            callback: static fn(string $value): string => rawurlencode($value),
            array: $segments,
        ));
    }

    /**
     * Resolves `$route` to a URL. A string is returned exactly as given — use it for a literal path (`/`) or an
     * external URL (`https://example.com`). An array is treated as a route and built into one: `controller`
     * (required) and `action` (defaults to `index`) as string keys, with additional integer keys as route
     * parameters.
     *
     * The one place this logic lives — `HtmlHelper::url()` and `Controller::redirect()` both delegate to this
     * instead of each having their own copy of it.
     *
     * @param array<string|int, string|int|float|bool>|string $route
     * @return string The resolved URL.
     * @throws \Elone\Core\Exception\RouteNotFoundException If `$route` is an array with invalid or missing
     *  parameters.
     */
    public static function resolve(array|string $route): string
    {
        if (is_string($route)) {
            return $route;
        }

        $controller = $route['controller'] ?? null;
        $action = $route['action'] ?? 'index';

        if (!is_string($controller) || !is_string($action)) {
            throw new RouteNotFoundException('Invalid route.');
        }

        $params = [];

        foreach ($route as $key => $value) {
            if (is_string($key) && !in_array($key, ['controller', 'action'], true)) {
                throw new RouteNotFoundException("Invalid route parameter: `$key`.");
            }

            if (is_int($key)) {
                $params[] = (string)$value;
            }
        }

        return new self(controller: $controller, action: $action, params: $params)->path();
    }
}
