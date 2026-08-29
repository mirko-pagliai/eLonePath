<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\HttpException;
use App\Core\Server\Request;

final class Router
{
    /**
     * @return array{controller: string, action: string, params: list<string>}
     */
    public function dispatch(Request $request): array
    {
        $segments = $this->segments($request->path());

        $controller = $segments[0] ?? 'home';
        $action = $segments[1] ?? 'index';
        $params = array_slice($segments, 2);

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
