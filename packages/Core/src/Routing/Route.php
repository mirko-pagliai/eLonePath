<?php
declare(strict_types=1);

namespace Elone\Core\Routing;

use Elone\Core\Controller;
use Elone\Core\Exception\ActionNotFoundException;
use Elone\Core\Exception\ControllerNotFoundException;
use Elone\Core\Exception\RouteNotFoundException;
use ReflectionMethod;

readonly class Route
{
    /**
     * @param list<string> $params
     */
    public function __construct(
        public string $controller,
        public string $action,
        private string $controllerNamespace,
        public array $params = [],
    ) {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $controller)) {
            throw new RouteNotFoundException("Invalid controller name: `$controller`.");
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
            throw new RouteNotFoundException("Action is not public: `$controllerClass::$action()`.");
        }
    }

    /**
     * @return class-string<\Elone\Core\Controller>
     */
    public function controllerClass(): string
    {
        $name = str_replace(
            ' ',
            '',
            ucwords(str_replace(['-', '_'], ' ', $this->controller)),
        );

        /** @var class-string<\Elone\Core\Controller> $className */
        $className = $this->controllerNamespace . "\\Controller\\{$name}Controller";

        return $className;
    }

    public function path(): string
    {
        $segments = [
            strtolower($this->controller),
            $this->action,
            ...$this->params,
        ];

        return '/' . implode('/', array_map(
            callback: static fn(string $value): string => rawurlencode($value),
            array: $segments,
        ));
    }
}
