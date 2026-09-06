<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

/**
 * Builds a `<form>`, one labeled input at a time — `create()`/`end()` open and close the tag, `input()` wraps a
 * single labeled field in the same `.mb-3` div `templates/Story/character.php` already hand-wrote. Deliberately
 * minimal: no label-from-field-name guessing, no validation-aware error display, no select/checkbox/radio
 * helpers — those get built the moment an actual form in this app needs one, not before.
 *
 * Generic enough to live here, in Core, alongside `HtmlHelper` — nothing about opening a `<form>` tag or wrapping
 * a labeled `<input>` is specific to this app's own story/character concerns.
 */
final class FormHelper extends Helper
{
    /**
     * Opens a `<form>` tag that POSTs to `$url` — no application built on this framework has needed another kind
     * of form yet, so `method="post"` isn't a parameter.
     *
     * @param array<string|int, string|int|float|bool>|string $url A literal URL/path, or a route array — see
     *  `Elone\Core\Routing\Route::resolve()`.
     * @param array<string, string|int|float|bool> $options Extra HTML attributes for the `<form>` tag.
     * @return string The opening `<form>` tag.
     * @throws \Elone\Core\Exception\RouteNotFoundException If `$url` is an array route with invalid or missing
     *  parameters.
     */
    public function create(array|string $url, array $options = []): string
    {
        return sprintf(
            '<form method="post" action="%s"%s>',
            h($this->url($url), ENT_QUOTES),
            $this->parseHtmlAttributes($options),
        );
    }

    /**
     * A single labeled `<input>`, wrapped in the same `mb-3` div every field in this app already uses.
     *
     * @param string $name Both the field's `name`/`id` attribute and, via `for`, what its `<label>` points to.
     * @param string $label The visible label text.
     * @param array<string, string|int|float|bool> $options Extra `<input>` attributes — `type` (defaults to
     *  `'text'`), `min`, `max`, `required`, and so on.
     * @return string The rendered `<div class="mb-3">...</div>` block.
     */
    public function input(string $name, string $label, array $options = []): string
    {
        $type = (string)($options['type'] ?? 'text');
        unset($options['type']);

        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, ...$options]);

        return sprintf(
            '<div class="mb-3"><label for="%s" class="form-label">%s</label>'
            . '<input type="%s" class="form-control"%s></div>',
            h($name, ENT_QUOTES),
            h($label),
            h($type, ENT_QUOTES),
            $attributes,
        );
    }

    /**
     * Closes a `<form>` opened with `create()`.
     */
    public function end(): string
    {
        return '</form>';
    }
}
