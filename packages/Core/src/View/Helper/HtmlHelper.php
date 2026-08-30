<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\Routing\Route;

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

        return new Route($controller, $action, $params)->path();
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
