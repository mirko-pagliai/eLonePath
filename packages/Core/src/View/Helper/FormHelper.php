<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

/**
 * Builds a `<form>`, one labeled input at a time — `create()`/`end()` open and close the tag, `input()` wraps a
 * single labeled field in a `.mb-3` div, `submit()`, `reset()`, and `hidden()` cover the other pieces almost
 * every form needs. No label-from-field-name guessing, no validation-aware error display, no
 * select/checkbox/radio helpers.
 */
final class FormHelper extends Helper
{
    /**
     * Opens a `<form>` tag that POSTs to `$url` — `method="post"` isn't a parameter, it's always POST.
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
     * A single labeled `<input>`, wrapped in a `.mb-3` div.
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

        if (($options['required'] ?? false) === true) {
            $options['required'] = 'required';
        }

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
     * A hidden `<input>` — for carrying a value along with the form without showing it to the user.
     *
     * @param string $name Both the field's `name` and `id` attribute.
     * @param string $value The field's value.
     * @param array<string, string|int|float|bool> $options Extra `<input>` attributes.
     * @return string The rendered `<input type="hidden">` tag.
     */
    public function hidden(string $name, string $value, array $options = []): string
    {
        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, 'value' => $value, ...$options]);

        return "<input type=\"hidden\"$attributes>";
    }

    /**
     * A generic `<button>` — what `submit()` and `reset()` both build on. `type` defaults to `'button'`: a bare
     * HTML `<button>` defaults to `'submit'`, which silently submits the enclosing form — surprising for
     * anything that isn't meant to (a button wired to its own JS handler, say).
     *
     * @param string $label The button's visible text (or, with `escape: false`, raw HTML).
     * @param array<string, string|int|float|bool> $options Extra `<button>` attributes — `type`, `class`, and so
     *  on. `escape` (default `true`) controls whether `$label` is escaped.
     * @return string The rendered `<button>` tag.
     */
    public function button(string $label, array $options = []): string
    {
        $type = (string)($options['type'] ?? 'button');
        unset($options['type']);

        $escape = (bool)($options['escape'] ?? true);
        unset($options['escape']);

        return sprintf(
            '<button type="%s"%s>%s</button>',
            h($type, ENT_QUOTES),
            $this->parseHtmlAttributes($options),
            $escape ? h($label) : $label,
        );
    }

    /**
     * A submit button — see `button()`.
     *
     * @param string $label The button's visible text.
     * @param array<string, string|int|float|bool> $options Extra `<button>` attributes.
     * @return string The rendered `<button type="submit">` tag.
     */
    public function submit(string $label = 'Submit', array $options = []): string
    {
        return $this->button($label, ['type' => 'submit', ...$options]);
    }

    /**
     * A reset button — see `button()`.
     *
     * @param string $label The button's visible text.
     * @param array<string, string|int|float|bool> $options Extra `<button>` attributes.
     * @return string The rendered `<button type="reset">` tag.
     */
    public function reset(string $label = 'Reset', array $options = []): string
    {
        return $this->button($label, ['type' => 'reset', ...$options]);
    }

    /**
     * Closes a `<form>` opened with `create()`.
     */
    public function end(): string
    {
        return '</form>';
    }
}
