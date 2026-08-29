<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\RouteNotFoundException;
use App\Core\Server\Request;

final class Router
{
    /**
     * @param \App\Core\Server\Request $request The HTTP request instance containing the path information.
     * @return array{controller: class-string<\App\Core\Controller>, action: string, params: list<string>} Associative
     * array containing the resolved 'controller' class, 'action' method, and 'params' to be passed to the action.
     */
    public function dispatch(Request $request): array
    {
        $segments = $this->segments($request->path());

        // Default behavior with unspecified controller and action.
        if (empty($segments[0]) && empty($segments[1])) {
            $controller = 'pages';
            $action = 'home';
        } else {
            $controller = $segments[0];
            $action = $segments[1] ?? 'index';
        }

        $params = array_slice($segments, 2);

        return $this->resolve($controller, $action, $params);
    }

    /**
     * @param list<string> $params
     * @return array{controller: class-string<\App\Core\Controller>, action: string, params: list<string>}
     */
    public function resolve(string $controller, string $action, array $params = []): array
    {
        return [
            'controller' => $this->controllerClass($controller),
            'action' => $action,
            'params' => $params,
        ];
    }

    /**
     * @return list<string>
     */
    private function segments(string $path): array
    {
        $path = trim($path, '/');

        if ($path === '') {
            return [];
        }

        return array_values(
            array_filter(
                array: explode('/', $path),
                callback: fn(string $segment): bool => $segment !== '',
            ),
        );
    }

    /**
     * @param string $name
     * @return class-string<\App\Core\Controller>
     */
    private function controllerClass(string $name): string
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $name)) {
            throw new RouteNotFoundException("Invalid controller name: $name.");
        }

        $name = str_replace(
            ' ',
            '',
            ucwords(str_replace(['-', '_'], ' ', $name)),
        );

        /** @var class-string<\App\Core\Controller> $className */
        $className = "App\\Controller\\{$name}Controller";

        return $className;
    }
}
