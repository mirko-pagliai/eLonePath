<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\Routing\Route;

/**
 * Generates HTML tags for various purposes, such as images, links, and icons.
 */
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
                h($attributeName, ENT_QUOTES),
                h((string)$value, ENT_QUOTES),
            );
        }

        return $htmlAttributes;
    }

    /**
     * Generates an `<i>` tag with the appropriate classes for a Bootstrap icon. The method formats
     * the icon name and merges it with additional class names provided in `$options`. Every other
     * entry in `$options` is applied as an attribute on the `<i>` tag.
     *
     * @param string $name The name of the icon, which can include or omit the "bi-" prefix.
     * @param array<string, string|int|float|bool> $options Extra HTML attributes for the `<i>` tag; `class` is merged
     * with the icon's own classes rather than overwritten.
     * @return string The generated `<i>` tag as a string, ready for inclusion in HTML.
     */
    public function icon(string $name, array $options = []): string
    {
        $name = trim($name);

        if (str_starts_with($name, 'bi bi-')) {
            $name = substr($name, strlen('bi bi-'));
        } elseif (str_starts_with($name, 'bi-')) {
            $name = substr($name, strlen('bi-'));
        }

        $class = "bi bi-$name";

        if (isset($options['class'])) {
            $class .= ' ' . $options['class'];
            unset($options['class']);
        }

        $htmlAttributes = $this->parseHtmlAttributes($options);

        return sprintf('<i class="%s"%s></i>', h($class, ENT_QUOTES), $htmlAttributes);
    }

    /**
     * Builds a `<img>` tag for the given source path. `$path` is not run through `url()` — pass it exactly as it
     * should appear in `src`, since images are served as static files rather than routed. Pass `alt` via `$options`
     * whenever the image conveys, meaning that it isn't already in the surrounding text.
     *
     * @param string $path The image's `src`, used exactly as given.
     * @param array<string, string|int|float|bool> $options Extra HTML attributes for the `<img>` tag.
     * @return string The generated `<img>` tag as a string.
     */
    public function image(string $path, array $options = []): string
    {
        $htmlAttributes = $this->parseHtmlAttributes($options);

        return sprintf(
            '<img src="%s"%s>',
            h($path, ENT_QUOTES),
            $htmlAttributes,
        );
    }

    /**
     * Generates a `<a>` tag with the given text, URL, and options. `$url` is resolved via `url()` — pass a
     * literal path or external URL as a string (`/`, `https://example.com`), or a route array to build one.
     *
     * `$options` accepts one known key, `escape` (bool, default `true`): whether `$text` is HTML-escaped before
     * being inserted. Leave it on for anything that isn't fully trusted, developer-written markup — story
     * content, anything sourced from data — and turn it off only to embed literal HTML you wrote yourself, such
     * as an icon. Every other key in `$options` is applied as an HTML attribute on the `<a>` tag (`class`, `id`,
     * `target`, and so on).
     *
     * @param string $text The text (or, with `escape: false`, raw HTML) to display within the anchor tag.
     * @param array<string|int, string|int|float|bool>|string $url A literal URL/path, or a route array (see
     *  `url()`).
     * @param array<string, string|int|float|bool> $options See above — `escape`, plus any HTML attribute.
     * @return string The rendered HTML `<a>` tag.
     * @throws \Elone\Core\Exception\RouteNotFoundException If `$url` is an array route with invalid or missing
     *  parameters.
     */
    public function link(string $text, array|string $url, array $options = []): string
    {
        $escape = (bool)($options['escape'] ?? true);
        unset($options['escape']);

        $htmlAttributes = $this->parseHtmlAttributes($options);

        return sprintf(
            '<a href="%s"%s>%s</a>',
            h($this->url($url), ENT_QUOTES),
            $htmlAttributes,
            $escape ? h($text) : $text,
        );
    }

    /**
     * Resolves `$route` to a URL — see `Route::resolve()`.
     *
     * @param array<string|int, string|int|float|bool>|string $route A literal URL/path, or a route array.
     * @return string The resolved URL.
     * @throws \Elone\Core\Exception\RouteNotFoundException If given an array route with invalid or missing parameters.
     * @see \Elone\Core\Routing\Route::resolve()
     */
    public function url(array|string $route): string
    {
        return Route::resolve($route);
    }
}
