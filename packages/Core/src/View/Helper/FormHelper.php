<?php
declare(strict_types=1);

namespace Elone\Core\View\Helper;

/**
 * Builds a `<form>`.
 *
 * `input()`, `select()`, `checkbox()`, and `radio()` each build only the bare field itself, no label, no wrapping
 * container.
 *
 * `control()` is the one method that builds the complete thing — label, field, and container — by calling whichever of
 * those the given `type` needs.
 *
 * `create()`/`end()` open and close the `<form>` tag; `submit()`, `reset()`, and `hidden()` cover the other pieces
 * almost every form needs.
 *
 * Every method builds its markup from `$config['templates']`, not a hardcoded string — give the constructor a
 * `templates` override to customize one or more entries.
 */
final class FormHelper extends Helper
{
    /**
     * @var array<string, mixed>
     */
    protected array $config = [
        'templates' => [
            'button' => '<button type="{{type}}"{{attrs}}>{{text}}</button>',
            'check' => '<input class="form-check-input" type="{{type}}" id="{{id}}" name="{{name}}" value="{{value}}"{{attrs}}>',
            'checkContainer' => '<div class="form-check">{{input}}{{label}}</div>',
            'checkLabel' => '<label{{attrs}}>{{text}}</label>',
            'formStart' => '<form method="post" action="{{action}}"{{attrs}}>',
            'formEnd' => '</form>',
            'hiddenInput' => '<input type="hidden"{{attrs}}>',
            'input' => '<input type="{{type}}" class="form-control"{{attrs}}>',
            'inputContainer' => '<div class="mb-3 {{type}}{{required}}">{{label}}{{input}}</div>',
            'label' => '<label{{attrs}}>{{text}}</label>',
            'option' => '<option value="{{value}}"{{selected}}>{{text}}</option>',
            'select' => '<select class="form-select"{{attrs}}>{{options}}</select>',
        ],
    ];

    /**
     * Opens a `<form>` tag that POSTs to `$url` — `method="post"` isn't a parameter, it's always POST.
     *
     * See `$config['templates']['formStart']`.
     *
     * @param array<string|int, string|int|float|bool>|string $url A literal URL/path, or a route array.
     * @param array<string, string|int|float|bool> $options Extra attributes for the `<form>` tag.
     * @return string The opening tag.
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
     * A `<label>` tag pointing at `$for` via its `for` attribute.
     *
     * See `$config['templates']['label']`.
     *
     * @param string $for The `id`/`name` of the field this label is for.
     * @param string $text The label text.
     * @return string The rendered tag.
     */
    public function label(string $for, string $text): string
    {
        return $this->formatTemplate('label', [
            'attrs' => $this->parseHtmlAttributes(['for' => $for, 'class' => 'form-label']),
            'text' => h($text),
        ]);
    }

    /**
     * A bare `<input>`.
     *
     * See `$config['templates']['input']`.
     *
     * Unless `$options` already has its own `value`, the field is pre-filled from the current request's own
     * submitted data — under `$name` — if there is one. This is what makes a form re-show what was typed after
     * a failed validation, without the controller or template having to wire it up.
     *
     * @param string $name Both the field's `name` and `id` attribute.
     * @param array<string, string|int|float|bool> $options Extra attributes — `type` (defaults to `'text'`), `min`,
     * `max`, `required`, `value`, and so on.
     * @return string The rendered tag.
     */
    public function input(string $name, array $options = []): string
    {
        $type = (string)($options['type'] ?? 'text');
        unset($options['type']);

        if (!array_key_exists('value', $options)) {
            $value = $this->view->getRequest()?->dataParam($name);
            if (is_scalar($value)) {
                $options['value'] = $value;
            }
        }

        if (($options['required'] ?? false) === true) {
            $options['required'] = 'required';
        }

        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, ...$options]);

        return $this->formatTemplate('input', [
            'type' => h($type, ENT_QUOTES),
            'attrs' => $attributes,
        ]);
    }

    /**
     * A bare `<select>`.
     *
     * See `$config['templates']['select']` (each `<option>` comes from `$config['templates']['option']`).
     *
     * Unless `$options` already has its own `value`, the selected option is read from the current request's own
     * submitted data — under `$name` — if there is one, the same way `input()`'s own value is.
     *
     * @param string $name Both the field's `name` and `id` attribute.
     * @param array<int|string, string> $choices Value => visible text, one `<option>` per entry.
     * @param array<string, string|int|float|bool> $options Extra attributes — `required`, `value` (the option to
     * pre-select), `multiple`, and so on.
     * @return string The rendered tag.
     */
    public function select(string $name, array $choices, array $options = []): string
    {
        if (!array_key_exists('value', $options)) {
            $value = $this->view->getRequest()?->dataParam($name);
            if (is_scalar($value)) {
                $options['value'] = $value;
            }
        }

        $selected = array_key_exists('value', $options) ? (string)$options['value'] : null;
        unset($options['value']);

        if (($options['required'] ?? false) === true) {
            $options['required'] = 'required';
        }

        $optionsHtml = '';
        foreach ($choices as $choiceValue => $text) {
            $optionsHtml .= $this->formatTemplate('option', [
                'value' => h((string)$choiceValue, ENT_QUOTES),
                'selected' => (string)$choiceValue === $selected ? ' selected' : '',
                'text' => h($text),
            ]);
        }

        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, ...$options]);

        return $this->formatTemplate('select', [
            'attrs' => $attributes,
            'options' => $optionsHtml,
        ]);
    }

    /**
     * A bare checkbox `<input>`.
     *
     * See `$config['templates']['check']`.
     *
     * @param string $name Both the field's `name` and `id` attribute.
     * @param string $value The value submitted when this checkbox is checked — unchecked, it submits nothing
     *  at all, per how HTML checkboxes always behave.
     * @param array<string, string|int|float|bool> $options Extra attributes — `checked` (bool, marks it
     *  pre-checked).
     * @return string The rendered tag.
     */
    public function checkbox(string $name, string $value = '1', array $options = []): string
    {
        if (($options['checked'] ?? false) === true) {
            $options['checked'] = 'checked';
        }

        return $this->formatTemplate('check', [
            'type' => 'checkbox',
            'id' => h($name, ENT_QUOTES),
            'name' => h($name, ENT_QUOTES),
            'value' => h($value, ENT_QUOTES),
            'attrs' => $this->parseHtmlAttributes($options),
        ]);
    }

    /**
     * One bare radio `<input>`.
     *
     * See `$config['templates']['check']` — the same template `checkbox()` uses, with `type: 'radio'`.
     *
     * @param string $name Shared by every radio in the same group — only the checked one's `$value` is
     *  submitted under it.
     * @param string $value This radio's own value.
     * @param array<string, string|int|float|bool> $options Extra attributes — `id` (defaults to `"$name-$value"`,
     * since every radio in a group needs a distinct one), `checked` (bool).
     * @return string The rendered tag.
     */
    public function radio(string $name, string $value, array $options = []): string
    {
        $id = (string)($options['id'] ?? "$name-$value");
        unset($options['id']);

        if (($options['checked'] ?? false) === true) {
            $options['checked'] = 'checked';
        }

        return $this->formatTemplate('check', [
            'type' => 'radio',
            'id' => h($id, ENT_QUOTES),
            'name' => h($name, ENT_QUOTES),
            'value' => h($value, ENT_QUOTES),
            'attrs' => $this->parseHtmlAttributes($options),
        ]);
    }

    /**
     * A complete, labeled field — the counterpart to `input()`/`select()`/`checkbox()`/`radio()`, which each
     * build only the bare field.
     *
     * Picks which of those to call from `$options['type']` (defaults to `'text'`), then wraps the result in
     * `$config['templates']['inputContainer']` alongside `label()`'s own output — except for `'checkbox'`,
     * wrapped in `$config['templates']['checkContainer']` instead, and `'radio'`, which builds one such block per
     * `$options['choices']` entry before wrapping all of them together in `inputContainer`.
     *
     * @param string $name Both the field's `name`/`id` attribute (or, for `'radio'`, every choice's shared
     *  `name`) and, via `for`, what its `<label>` points to.
     * @param string $label The visible label text.
     * @param array<string, mixed> $options `type` (defaults to `'text'`); `choices` (value => visible text),
     *  required for `'select'`/`'radio'`; anything else is forwarded to the field method `type` selects.
     * @return string The rendered field, complete with label and container.
     */
    public function control(string $name, string $label, array $options = []): string
    {
        $type = $options['type'] ?? 'text';
        $type = is_string($type) ? $type : 'text';
        unset($options['type']);

        if ($type === 'checkbox') {
            return $this->checkboxControl($name, $label, $options);
        }

        if ($type === 'radio') {
            /** @var array<int|string, string> $choices */
            $choices = $options['choices'] ?? [];
            unset($options['choices']);

            return $this->radioControl($name, $label, $choices, $options);
        }

        $required = ($options['required'] ?? false) === true;

        if ($type === 'select') {
            /** @var array<int|string, string> $choices */
            $choices = $options['choices'] ?? [];
            unset($options['choices']);

            /** @var array<string, string|int|float|bool> $options */
            $field = $this->select($name, $choices, $options);
        } else {
            /** @var array<string, string|int|float|bool> $options */
            $field = $this->input($name, ['type' => $type, ...$options]);
        }

        return $this->formatTemplate('inputContainer', [
            'type' => h($type, ENT_QUOTES),
            'required' => $required ? ' required' : '',
            'label' => $this->label($name, $label),
            'input' => $field,
        ]);
    }

    /**
     * The `type: 'checkbox'` branch of `control()`.
     *
     * See `$config['templates']['checkContainer']`.
     *
     * @param string $name
     * @param string $label
     * @param array<string, mixed> $options
     * @return string
     */
    private function checkboxControl(string $name, string $label, array $options): string
    {
        $value = $options['value'] ?? '1';
        $value = is_scalar($value) ? (string)$value : '1';
        unset($options['value']);

        /** @var array<string, string|int|float|bool> $options */
        $input = $this->checkbox($name, $value, $options);

        return $this->formatTemplate('checkContainer', [
            'input' => $input,
            'label' => $this->checkLabel($name, $label),
        ]);
    }

    /**
     * The `type: 'radio'` branch of `control()` — one `.form-check` block per `$choices` entry, all sharing
     * `$name`, wrapped together in `$config['templates']['inputContainer']`. Each block itself uses
     * `$config['templates']['checkContainer']`, the same as `checkboxControl()`.
     *
     * @param string $name
     * @param string $label
     * @param array<int|string, string> $choices
     * @param array<string, mixed> $options
     * @return string
     */
    private function radioControl(string $name, string $label, array $choices, array $options): string
    {
        $required = ($options['required'] ?? false) === true;
        unset($options['required']);

        $selectedValue = $options['value'] ?? null;
        $selected = is_scalar($selectedValue) ? (string)$selectedValue : null;
        unset($options['value']);

        $html = '';
        foreach ($choices as $choiceValue => $text) {
            $choiceOptions = $options;
            if ((string)$choiceValue === $selected) {
                $choiceOptions['checked'] = true;
            }

            /** @var array<string, string|int|float|bool> $choiceOptions */
            $input = $this->radio($name, (string)$choiceValue, $choiceOptions);

            $id = (string)($choiceOptions['id'] ?? "$name-$choiceValue");
            $checkLabel = $this->checkLabel($id, (string)$text);

            $html .= $this->formatTemplate('checkContainer', ['input' => $input, 'label' => $checkLabel]);
        }

        return $this->formatTemplate('inputContainer', [
            'type' => 'radio',
            'required' => $required ? ' required' : '',
            'label' => $this->label($name, $label),
            'input' => $html,
        ]);
    }

    /**
     * A `<label>` styled for a checkbox/radio (`$config['templates']['checkLabel']`, not `['label']`) — its own
     * template, kept separate from `label()`'s, so customizing one never touches the other.
     *
     * @param string $for
     * @param string $text
     * @return string
     */
    private function checkLabel(string $for, string $text): string
    {
        return $this->formatTemplate('checkLabel', [
            'attrs' => $this->parseHtmlAttributes(['for' => $for, 'class' => 'form-check-label']),
            'text' => h($text),
        ]);
    }

    /**
     * A hidden `<input>` — for carrying a value along with the form without showing it to the user.
     *
     * See `$config['templates']['hiddenInput']`.
     *
     * @param string $name Both the field's `name` and `id` attribute.
     * @param string $value The field's value.
     * @param array<string, string|int|float|bool> $options Extra attributes.
     * @return string The rendered tag.
     */
    public function hidden(string $name, string $value, array $options = []): string
    {
        $attributes = $this->parseHtmlAttributes(['id' => $name, 'name' => $name, 'value' => $value, ...$options]);

        return $this->formatTemplate('hiddenInput', ['attrs' => $attributes]);
    }

    /**
     * A generic `<button>` — what `submit()` and `reset()` both build on. `type` defaults to `'button'`: a bare
     * HTML `<button>` defaults to `'submit'`, which silently submits the enclosing form — surprising for
     * anything that isn't meant to (a button wired to its own JS handler, say).
     *
     * See `$config['templates']['button']`.
     *
     * @param string $label The button's visible text (or, with `escape: false`, raw HTML).
     * @param array<string, string|int|float|bool> $options Extra attributes — `type`, `class`, and so on. `escape`
     * (default `true`) controls whether `$label` is escaped.
     * @return string The rendered tag.
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
     * A submit button.
     *
     * @param string $label The button's visible text.
     * @param array<string, string|int|float|bool> $options Extra attributes.
     * @return string The rendered tag.
     */
    public function submit(string $label = 'Submit', array $options = []): string
    {
        return $this->button($label, ['type' => 'submit', ...$options]);
    }

    /**
     * A reset button.
     *
     * @param string $label The button's visible text.
     * @param array<string, string|int|float|bool> $options Extra attributes.
     * @return string The rendered tag.
     */
    public function reset(string $label = 'Reset', array $options = []): string
    {
        return $this->button($label, ['type' => 'reset', ...$options]);
    }

    /**
     * Closes a `<form>` opened with `create()`.
     *
     * See `$config['templates']['formEnd']`.
     *
     * @return string The closing tag.
     */
    public function end(): string
    {
        return $this->formatTemplate('formEnd', []);
    }
}
