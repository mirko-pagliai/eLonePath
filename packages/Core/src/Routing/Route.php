<?php
declare(strict_types=1);

namespace Elone\Core\Routing;

use Elone\Core\Controller;
use Elone\Core\Exception\ActionNotFoundException;
use Elone\Core\Exception\ControllerNotFoundException;
use Elone\Core\Exception\RouteNotFoundException;
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
     *  Callers deriving it from raw, untrusted input — a URL segment — must normalize it (see `ControllerName`) before
     *  constructing a `Route`.
     * @param string $action The name of the action method within the controller to be executed.
     * @param list<string> $params Optional parameters to be passed to the action method.
     *
     * @return void
     *
     * @throws \Elone\Core\Exception\ControllerNotFoundException If the controller name is invalid or the specified
     * controller class is not found.
     * @throws \Elone\Core\Exception\RouteNotFoundException If the controller class does not extend the base
     * `Controller` class.
     * @throws \Elone\Core\Exception\ActionNotFoundException If the specified action is not found in the controller
     * class or is not public.
     */
    public function __construct(
        public string $controller,
        public string $action,
        public array $params = [],
    ) {
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
     * The controller name is converted to kebab-case, while all segments are URL-encoded to ensure safe inclusion in
     * the URL. The segments are then concatenated using slashes to form the complete path.
     *
     * @return string The generated URL path, starting with a forward slash (`/`).
     */
    public function path(): string
    {
        $segments = [
            new ControllerName($this->controller)->kebabCase(),
            $this->action,
            ...$this->params,
        ];

        return '/' . implode('/', array_map(
            callback: static fn(string $value): string => rawurlencode($value),
            array: $segments,
        ));
    }
}
