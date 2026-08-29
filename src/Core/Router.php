<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\RouteNotFoundException;
use App\Core\Server\Request;

final class Router
{
    /**
     * @param \App\Core\Server\Request $request The HTTP request instance containing the path information.
     * @return array{controller: string, action: string, params: list<string>} An associative array containing the
     * resolved 'controller' class, 'action' method, and 'params' to be passed to the action.*/
    public function dispatch(Request $request): array
    {
        $segments = $this->segments($request->path());

        // Default behavior with unspecified controller and action.
        if (empty($segments[0]) && empty($segments[1])) {
            $controller = 'pages';
            $action = 'home';
        } else {
            $controller = $segments[0] ?? 'pages';
            $action = $segments[1] ?? 'index';
        }

        $params = array_slice($segments, 2);

        return $this->resolve($controller, $action, $params);
    }

    /**
     * @param list<string> $params
     * @return array{controller: string, action: string, params: list<string>}
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
                explode('/', $path),
                static fn(string $segment): bool => $segment !== '',
            ),
        );
    }

    private function controllerClass(string $name): string
    {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_-]*$/', $name)) {
            throw new RouteNotFoundException("Invalid controller name: {$name}");
        }

        $name = str_replace(
            ' ',
            '',
            ucwords(str_replace(['-', '_'], ' ', $name)),
        );

        return 'App\\Controller\\' . $name . 'Controller';
    }
}
