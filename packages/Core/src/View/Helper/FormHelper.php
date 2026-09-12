<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

use Elone\Core\View\View;

/**
 * Builds a `<form>`, one labeled input at a time — `create()`/`end()` open and close the tag, `input()` wraps a
 * single labeled field in a `.mb-3` div, `submit()`, `reset()`, and `hidden()` cover the other pieces almost
 * every form needs. No label-from-field-name guessing, no validation-aware error display, no
 * select/checkbox/radio helpers.
 *
 * Every method builds its markup from `$templates`, not a hardcoded string — pass a `templates` override to the
 * constructor to customize one or more entries, e.g. `new FormHelper($view, ['templates' => ['label' => '...']])`.
 */
final class FormHelper extends Helper
{
    /**
     * @var array<string, string>
     */
    protected array $templates = [
        'button' => '<button type="{{type}}"{{attrs}}>{{text}}</button>',
        'formStart' => '<form method="post" action="{{action}}"{{attrs}}>',
        'formEnd' => '</form>',
        'hiddenInput' => '<input type="hidden"{{attrs}}>',
        'input' => '<input type="{{type}}" class="form-control"{{attrs}}>',
        'inputContainer' => '<div class="mb-3 {{type}}{{required}}">{{label}}{{input}}</div>',
        'label' => '<label{{attrs}}>{{text}}</label>',
    ];

    /**
     * @param \Elone\Core\View\View $view
     * @param array{templates?: array<string, string>, ...<string, mixed>} $config Currently the only supported key is
     * `templates`: overrides for one or more of the default `$templates` entries — entries not mentioned there keep
     * their default.
     */
    public function __construct(View $view, array $config = [])
    {
        parent::__construct($view);

        $this->templates = [...$this->templates, ...($config['templates'] ?? [])];
    }

    /**
     * Renders `$this->templates[$name]`, replacing each `{{key}}` placeholder with `$data[key]` — or with an
     * empty string, for a placeholder `$data` doesn't have an entry for.
     *
     * @param string $name A key into `$this->templates`.
     * @param array<string, string> $data
     * @return string
     */
    private function formatTemplate(string $name, array $data): string
    {
        $search = array_map(
            callback: static fn(string $key): string => "{{{$key}}}",
            array: array_keys($data),
        );

        return str_replace(search: $search, replace: array_values($data), subject: $this->templates[$name]);
    }

    /**
     * Opens a `<form>` tag that POSTs to `$url` — `method="post"` isn't a parameter, it's always POST. See
     * `$templates['formStart']`.
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
        return $this->formatTemplate('formStart', [
            'action' => h($this->url($url), ENT_QUOTES),
            'attrs' => $this->parseHtmlAttributes($options),
        ]);
    }

    /**
     * A `<label>` tag pointing at `$for` via its `for` attribute — see `$templates['label']`.
     *
     * @param string $for The `id`/`name` of the field this label is for.
     * @param string $text The visible label text.
     * @return string The rendered `<label>` tag.
     */
    public function label(string $for, string $text): string
    {
        return $this->formatTemplate('label', [
            'attrs' => $this->parseHtmlAttributes(['for' => $for, 'class' => 'form-label']),
            'text' => h($text),
        ]);
    }

    /**
     * A single labeled `<input>`, wrapped via `$templates['inputContainer']` — the `<input>` tag itself comes
     * from `$templates['input']`. The container's own class always includes `$type` (`mb-3 text`, `mb-3
     * number`, and so on), plus `required` when the field is one.
     *
     * Unless `$options` already has its own `value`, the field is pre-filled from the current request's own
     * submitted data — under `$name` — if there is one. This is what makes a form re-show what was typed after
     * a failed validation, without the controller or template having to wire it up.
     *
     * @param string $name Both the field's `name`/`id` attribute and, via `for`, what its `<label>` points to.
     * @param string $label The visible label text.
     * @param array<string, string|int|float|bool> $options Extra `<input>` attributes — `type` (defaults to
     *  `'text'`), `min`, `max`, `required`, `value`, and so on.
     * @return string The rendered container block.
     */
    public function input(string $name, string $label, array $options = []): string
    {
        $type = (string)($options['type'] ?? 'text');
        unset($options['type']);

        if (!array_key_exists('value', $options)) {
            $value = $this->view->getRequest()?->dataParam($name);
            if (is_scalar($value)) {
                $options['value'] = $value;
            }
        }

        $required = ($options['required'] ?? false) === true;
        if ($required) {
            $options['required'] = 'required';
        }

        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, ...$options]);

        $input = $this->formatTemplate('input', [
            'type' => h($type, ENT_QUOTES),
            'attrs' => $attributes,
        ]);

        return $this->formatTemplate('inputContainer', [
            'type' => h($type, ENT_QUOTES),
            'required' => $required ? ' required' : '',
            'label' => $this->label($name, $label),
            'input' => $input,
        ]);
    }

    /**
     * A hidden `<input>` — for carrying a value along with the form without showing it to the user. See
     * `$templates['hiddenInput']`.
     *
     * @param string $name Both the field's `name` and `id` attribute.
     * @param string $value The field's value.
     * @param array<string, string|int|float|bool> $options Extra `<input>` attributes.
     * @return string The rendered hidden `<input>` tag.
     */
    public function hidden(string $name, string $value, array $options = []): string
    {
        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, 'value' => $value, ...$options]);

        return $this->formatTemplate('hiddenInput', ['attrs' => $attributes]);
    }

    /**
     * A generic `<button>` — what `submit()` and `reset()` both build on. `type` defaults to `'button'`: a bare
     * HTML `<button>` defaults to `'submit'`, which silently submits the enclosing form — surprising for
     * anything that isn't meant to (a button wired to its own JS handler, say). See `$templates['button']`.
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

        return $this->formatTemplate('button', [
            'type' => h($type, ENT_QUOTES),
            'attrs' => $this->parseHtmlAttributes($options),
            'text' => $escape ? h($label) : $label,
        ]);
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
     * Closes a `<form>` opened with `create()` — see `$templates['formEnd']`.
     *
     * @return string
     */
    public function end(): string
    {
        return $this->formatTemplate('formEnd', []);
    }
}
