<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\Exception\RouteNotFoundException;
use Elone\Core\Routing\Route;

final class HtmlHelper
{
    /**
     * Converts an associative array of HTML attributes into a formatted string suitable for insertion into an HTML tag.
     * Keys in the array represent attribute names, and their corresponding values represent the attribute values.
     *
     * @param array<string, string|int|float|bool> $attributes An associative array of attributes where keys are the
     * attribute names and values are the attribute values. Boolean values are converted to their string equivalents.
     *
     * @return string A properly formatted string of HTML attributes, where each attribute is escaped to ensure that
     * special characters do not break the resulting HTML.
     */
    protected function parseHtmlAttributes(array $attributes): string
    {
        $htmlAttributes = '';

        foreach ($attributes as $attributeName => $value) {
            $htmlAttributes .= sprintf(
                ' %s="%s"',
                htmlspecialchars($attributeName, ENT_QUOTES),
                htmlspecialchars((string)$value, ENT_QUOTES),
            );
        }

        return $htmlAttributes;
    }

    /**
     * Generates an `<i>` tag with the appropriate classes for a Bootstrap icon. The method formats
     * the icon name and merges it with additional class names provided in `$attributes`. All other
     * attributes will be applied to the `<i>` tag.
     *
     * @param string $name The name of the icon, which can include or omit the "bi-" prefix.
     * @param array<string, string|int|float|bool> $attributes
     * @return string The generated `<i>` tag as a string, ready for inclusion in HTML.
     */
    public function icon(string $name, array $attributes = []): string
    {
        $name = trim($name);

        if (str_starts_with($name, 'bi bi-')) {
            $name = substr($name, strlen('bi bi-'));
        } elseif (str_starts_with($name, 'bi-')) {
            $name = substr($name, strlen('bi-'));
        }

        $class = "bi bi-$name";

        if (isset($attributes['class'])) {
            $class .= ' ' . $attributes['class'];
            unset($attributes['class']);
        }

        $htmlAttributes = $this->parseHtmlAttributes($attributes);

        return sprintf('<i class="%s"%s></i>', htmlspecialchars($class, ENT_QUOTES), $htmlAttributes);
    }

    /**
     * Builds a `<img>` tag for the given source path. `$path` is not run through `url()` — pass it exactly as it
     * should appear in `src`, since images are served as static files rather than routed. Pass `alt` via
     * `$attributes` whenever the image conveys meaning that isn't already in the surrounding text.
     *
     * @param array<string, string|int|float|bool> $attributes
     */
    public function image(string $path, array $attributes = []): string
    {
        $htmlAttributes = $this->parseHtmlAttributes($attributes);

        return sprintf(
            '<img src="%s"%s>',
            htmlspecialchars($path, ENT_QUOTES),
            $htmlAttributes,
        );
    }

    /**
     * Generates a `<a>` tag with the given text, URL parameters, and HTML attributes. The `$params` array
     * is used to construct the `href` attribute using the `url()` method. Provides a way to define additional
     * attributes such as `class`, `id`, `target`, or others via `$attributes`.
     *
     * @param string $text The text to display within the anchor tag.
     * @param array<string|int, string|int|float|bool> $params The parameters used to build the URL.
     * @param array<string, string|int|float|bool> $attributes Additional HTML attributes to apply to the anchor tag.
     * @return string The rendered HTML `<a>` tag.
     */
    public function link(string $text, array $params, array $attributes = []): string
    {
        $htmlAttributes = $this->parseHtmlAttributes($attributes);

        return sprintf(
            '<a href="%s"%s>%s</a>',
            htmlspecialchars($this->url($params), ENT_QUOTES),
            $htmlAttributes,
            htmlspecialchars($text),
        );
    }

    /**
     * Generates a URL path based on the specified route. The `$route` array must contain at least a
     * `controller` key as a string and optionally an `action` key (defaulting to `index` if not provided).
     * Additional parameters in the route are validated and converted.
     *
     * @param array<string|int, string|int|float|bool> $route An associative array specifying the route. Keys
     * `controller` and `action` are required. Additional integer keys may be used for route parameters.
     * @return string The generated URL path based on the provided route.
     * @throws \Elone\Core\Exception\RouteNotFoundException If the route contains invalid or missing parameters.
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

        $route = new Route(
            controller: $controller,
            action: $action,
            params: $params,
        );

        return $route->path();
    }
}
