<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Michelf\Markdown;
use RuntimeException;

/**
 * Generates HTML tags for various purposes, such as images, links, and icons.
 */
class HtmlHelper extends Helper
{
    /**
     * Generates an `<i>` tag with the appropriate classes for a Bootstrap icon. The method formats the icon name and
     * merges it with additional class names provided in `$options`. Every other entry in `$options` is applied as an
     * attribute on the `<i>` tag.
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
     * Generates a `<a>` tag with the given text, URL, and options. `$url` is resolved via `url()` — pass a literal path
     * or external URL as a string (`/`, `https://example.com`), or a route array to build one; `$query` appends a
     * querystring the same way `url()` does.
     *
     * `$options` accepts two known keys: `escape` (bool, default `true`) — whether `$text` is HTML-escaped before
     * being inserted; leave it on for anything that isn't fully trusted, developer-written markup — content
     * sourced from data — and turn it off only to embed literal HTML you wrote yourself. `icon` (string) —
     * prepends the named icon to `$text`, see `icon()`. Every other key in `$options` is applied as an HTML
     * attribute on the `<a>` tag (`class`, `id`, `target`, and so on).
     *
     * @param string $text The text (or, with `escape: false`, raw HTML) to display within the anchor tag.
     * @param array<string|int, string|int|float|bool>|string $url A literal URL/path, or a route array (see
     *  `url()`).
     * @param array<string, string|int|float|bool> $options See above — `escape`, `icon`, plus any HTML attribute.
     * @param array<string, string|int|float|bool> $query Appended to `$url` as a querystring — see `url()`.
     * @return string The rendered HTML `<a>` tag.
     * @throws \Elone\Core\Exception\RouteNotFoundException If `$url` is an array route with invalid or missing
     *  parameters.
     */
    public function link(string $text, array|string $url, array $options = [], array $query = []): string
    {
        $escape = (bool)($options['escape'] ?? true);
        unset($options['escape']);

        $text = $this->prependIcon($escape ? h($text) : $text, $options);

        $htmlAttributes = $this->parseHtmlAttributes($options);

        return sprintf(
            '<a href="%s"%s>%s</a>',
            h($this->url($url, $query), ENT_QUOTES),
            $htmlAttributes,
            $text,
        );
    }

    /**
     * Builds a generic `<$name>...</$name>` tag, for anything not covered by a more specific method.
     *
     * `$options` accepts the same `escape` and `icon` keys as `link()` — see there. Every other key is applied as
     * an HTML attribute on the tag.
     *
     * @param string $name The tag name, e.g. `'span'`, `'p'`, `'h1'`.
     * @param string $text The tag's text content (or, with `escape: false`, raw HTML).
     * @param array<string, string|int|float|bool> $options See above — `escape`, `icon`, plus any HTML attribute.
     * @return string The rendered `<$name>...</$name>` tag.
     */
    public function tag(string $name, string $text, array $options = []): string
    {
        $escape = (bool)($options['escape'] ?? true);
        unset($options['escape']);

        $text = $this->prependIcon($escape ? h($text) : $text, $options);

        $htmlAttributes = $this->parseHtmlAttributes($options);

        return sprintf('<%s%s>%s</%s>', h($name, ENT_QUOTES), $htmlAttributes, $text, h($name, ENT_QUOTES));
    }

    /**
     * If `$options` has an `icon` key, removes it and returns `$text` with the corresponding icon — built via
     * `icon()` — prepended. Returns `$text` unchanged otherwise.
     *
     * @param string $text
     * @param array<string, string|int|float|bool> $options
     * @return string
     */
    private function prependIcon(string $text, array &$options): string
    {
        if (!isset($options['icon'])) {
            return $text;
        }

        $icon = (string)$options['icon'];
        unset($options['icon']);

        return $this->icon($icon) . ' ' . $text;
    }

    /**
     * @return bool
     * @internal Checks if the `michelf/php-markdown` package is available
     */
    protected function checkHasMarkdown(): bool
    {
        return class_exists(Markdown::class);
    }

    /**
     * Converts a markdown string into HTML.
     *
     * @param string $markdown The markdown text to be converted.
     * @return string The converted HTML string.
     * @throws \RuntimeException If the `michelf/php-markdown` package is not installed.
     */
    public function markdown(string $markdown): string
    {
        if (!$this->checkHasMarkdown()) {
            throw new RuntimeException(
                'Package `michelf/php-markdown` is required to use `' . __METHOD__ . '()`.',
            );
        }

        return Markdown::defaultTransform($markdown);
    }
}
