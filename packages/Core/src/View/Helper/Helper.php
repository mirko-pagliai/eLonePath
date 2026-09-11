<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\Routing\Route;
use Elone\Core\View\View;

/**
 * Base class every helper extends. Takes the `View` itself, not any specific other helper, in its constructor.
 * Any other helper this one needs (e.g. `$this->view->Html`) is reached lazily via `__get()`, not at construction
 * time.
 *
 * Only a class extending this one can be registered via `View::loadHelper()`; `Helper` itself is abstract.
 */
abstract class Helper
{
    public function __construct(protected readonly View $view)
    {
    }

    /**
     * Gives helper access to another helper loaded on the same view, as `$this->{$name}` — the same magic
     * `View::__get()` gives templates, just reached one level down.
     *
     * @throws \Elone\Core\Exception\HelperNotFoundException If no helper was registered under `$name`.
     */
    public function __get(string $name): Helper
    {
        return $this->view->{$name};
    }

    /**
     * Converts an associative array of HTML attributes into a formatted string suitable for insertion into an HTML
     * tag. Keys in the array represent attribute names, and their corresponding values represent the attribute
     * values.
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
     * Resolves `$route` to a URL, appending `$query` as a querystring — see `Route::resolve()`.
     *
     * @param array<string|int, string|int|float|bool>|string $route A literal URL/path, or a route array.
     * @param array<string, string|int|float|bool> $query Appended as `?key=value&...`. Empty (the default) adds
     *  nothing.
     * @return string The resolved URL.
     * @throws \Elone\Core\Exception\RouteNotFoundException If given an array route with invalid or missing parameters.
     * @see \Elone\Core\Routing\Route::resolve()
     */
    public function url(array|string $route, array $query = []): string
    {
        return Route::resolve(route: $route, query: $query);
    }
}
