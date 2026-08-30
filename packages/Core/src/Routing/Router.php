<?php
declare(strict_types=1);

namespace Elone\Core\Routing;

use Elone\Core\Configuration;
use Elone\Core\Server\Request;

final class Router
{
    public function __construct(private readonly Configuration $configuration)
    {
    }

    /**
     * Handles the routing of a given request by determining the controller, action, and parameters.
     *
     * @param \Elone\Core\Server\Request $request The incoming request containing the path to be dispatched.
     * @return \Elone\Core\Routing\Route Returns the resolved route for the given request.
     */
    public function dispatch(Request $request): Route
    {
        $segments = $this->segments($request->path());

        // Default behavior with unspecified controller and action.
        if (empty($segments[0])) {
            $controller = 'Pages';
            $action = 'home';
        } else {
            $controller = new ControllerName($segments[0])->studlyCase();
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
        return new Route($controller, $action, $this->configuration->namespace(), $params);
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
