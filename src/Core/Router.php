<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\HttpException;
use App\Core\Server\Request;

final class Router
{
    /**
     * Handles the dispatch process by resolving the controller, action, and parameters from the given request's path.
     *
     * It receives:
     * ```
     * /pages/view/123
     * ```
     * and divides it into segments:
     * ```
     * pages
     * view
     * 123
     * ```
     *
     * The returned result is:
     * ```
     *  [
     *      'controller' => 'App\\Controller\\PagesController',
     *      'action' => 'view',
     *      'params' => ['123'],
     *  ]
     * ```
     *
     * @param \App\Core\Server\Request $request The HTTP request instance containing the path information.
     * @return array{controller: string, action: string, params: list<string>} An associative array containing the
     * resolved 'controller' class, 'action' method, and 'params' to be passed to the action.
     */
    public function dispatch(Request $request): array
    {
        $segments = $this->segments($request->path());

        // Default behavior with unspecified controller and action.
        if (!$segments[0] && !$segments[1]) {
            $controller = 'pages';
            $action = 'home';
        } else {
            $controller = $segments[0] ?? 'pages';
            $action = $segments[1] ?? 'index';
        }

        $params = array_slice($segments, 2);

        return ['controller' => $this->controllerClass($controller)] + compact('action', 'params');
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
            throw new HttpException("Invalid controller name: {$name}");
        }

        $name = str_replace(
            ' ',
            '',
            ucwords(str_replace(['-', '_'], ' ', $name)),
        );

        return 'App\\Controller\\' . $name . 'Controller';
    }
}
