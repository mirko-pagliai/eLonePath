<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\Routing\Route;
use Elone\Core\View\View;

/**
 * Base class every helper extends. Takes the `View` itself, not any specific other helper, in its constructor —
 * the same way a template does. Any other helper this one needs (e.g. `$this->view->Html`) is reached lazily,
 * inside a method body, not at construction time: by the time any helper's own methods actually run, every helper
 * the view loads is already registered, regardless of which order they were loaded in — so this composes safely
 * even if helpers end up referencing each other in a cycle, which a constructor directly depending on another
 * helper's instance could not.
 *
 * Only a class extending this one can be registered via `View::loadHelper()` — the parameter type there enforces
 * it; `Helper` itself, being abstract, can never be instantiated directly.
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
     * Lives here, not on `HtmlHelper` alone, because it's a plain string-building utility with nothing HTML-helper
     * specific about it — `FormHelper::input()` needs exactly the same thing, for the same reason `icon()`,
     * `image()`, and `link()` do.
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
     * Lives here, not on `HtmlHelper` alone, for the same reason `parseHtmlAttributes()` does: resolving a route
     * to a URL has nothing to do with building HTML tags specifically. `HtmlHelper::link()` calls this exact
     * method on itself already (unaffected by living here instead — inheritance makes no difference to that call
     * site); `FormHelper::create()` needs it too, for a `<form>`'s own `action` attribute, without needing to load
     * the whole of `HtmlHelper` (icons, images, markdown) just to reach one unrelated method on it.
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
