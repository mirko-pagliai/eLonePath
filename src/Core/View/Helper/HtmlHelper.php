<?php
declare(strict_types=1);

namespace App\Core\View\Helper;

use App\Core\Dispatcher;
use App\Core\Exception\RouteNotFoundException;
use App\Core\Routing\Route;

final class HtmlHelper
{
    /**
     * @param array<string|int, string|int|float|bool> $route
     */
    public function url(array $route): string
    {
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

        $route = new Route($controller, $action, $params);

        $method = Dispatcher::getMethod($route);

        Dispatcher::resolveArguments($method, $route->params);

        return $route->path();
    }

    /**
     * @param array<string|int, string|int|float|bool> $route
     * @param array<string, string|int|float|bool> $attributes
     */
    public function link(string $text, array $route, array $attributes = []): string
    {
        $htmlAttributes = '';

        foreach ($attributes as $name => $value) {
            $htmlAttributes .= sprintf(
                ' %s="%s"',
                htmlspecialchars($name, ENT_QUOTES),
                htmlspecialchars((string)$value, ENT_QUOTES),
            );
        }

        return sprintf(
            '<a href="%s"%s>%s</a>',
            htmlspecialchars($this->url($route), ENT_QUOTES),
            $htmlAttributes,
            htmlspecialchars($text),
        );
    }
}
