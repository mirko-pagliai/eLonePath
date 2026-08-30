<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\Configuration;
use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\Routing\Route;

final class HtmlHelper
{
    public function __construct(private readonly Configuration $configuration)
    {
    }

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

        return new Route($controller, $action, $this->configuration->namespace(), $params)->path();
    }

    /**
     * Builds an `<img>` tag for the given source path. `$path` is not run through `url()` — pass it exactly as it
     * should appear in `src`, since images are served as static files rather than routed. Pass `alt` via
     * `$attributes` whenever the image conveys meaning that isn't already in the surrounding text.
     *
     * @param array<string, string|int|float|bool> $attributes
     */
    public function image(string $path, array $attributes = []): string
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
            '<img src="%s"%s>',
            htmlspecialchars($path, ENT_QUOTES),
            $htmlAttributes,
        );
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
