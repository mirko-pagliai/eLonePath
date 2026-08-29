<?php
declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Server\Request;

final class Router
{
    /**
     * Handles the routing of a given request by determining the controller, action, and parameters.
     *
     * @param \App\Core\Server\Request $request The incoming request containing the path to be dispatched.
     * @return \App\Core\Routing\Route Returns the resolved route for the given request.
     */
    public function dispatch(Request $request): Route
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
     */
    public function resolve(string $controller, string $action, array $params = []): Route
    {
        return new Route($controller, $action, $params);
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
}
