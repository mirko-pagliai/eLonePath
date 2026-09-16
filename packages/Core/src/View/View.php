<?php
declare(strict_types=1);

namespace Elone\Core\View;

use Elone\Core\Exception\HelperNotFoundException;
use Elone\Core\Exception\LayoutNotFoundException;
use Elone\Core\Exception\TemplateNotFoundException;
use Elone\Core\Server\Request;
use Elone\Core\View\Helper\Helper;
use LogicException;
use Throwable;

/**
 * Renders PHP templates.
 *
 * `render()` renders a full page — a template file, optionally wrapped in a layout — using the data previously passed
 * to `set()`. `element()` renders a smaller, reusable snippet from `templates/element/` for inclusion inside another
 * template, taking its own data explicitly rather than whatever's been `set()` on the view.
 *
 * Inside any template, `$this` is the `View` instance rendering it — giving access to every helper registered via
 * `loadHelper()` (e.g. `$this->Html`), and to `element()` for including further snippets. `View` itself loads no
 * helper of its own — not even a generic HTML one — since even that is a choice specific to an app's own domain;
 * an app's own `View` subclass decides which helpers to load, and with what.
 *
 * A template can hand its rendering off to another one via `extend()` — once it finishes evaluating, whatever it
 * called `extend()` with is rendered next, with the extending template's own output available as the `content`
 * block (and any other named block it defined via `start()`/`assign()`/`append()`/`prepend()`, read back with
 * `fetch()`). This can chain: the template `extend()` points to can itself call `extend()` again, and so on, each
 * step's own output becoming the next step's `content`. A layout is rendered the same way, and can `extend()` a
 * further layout in exactly the same fashion — this is how `templates/common/*.php` files (a shared wrapper several
 * templates each extend, defining only the parts that differ) and per-request content the layout itself reads back
 * (a page title, extra head markup, a sidebar) both work.
 */
class View
{
    /**
     * @var array<string, \Elone\Core\View\Helper\Helper>
     */
    private array $helpers = [];

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @var array<string, string>
     */
    private array $blocks = [];

    /**
     * The name of the block currently being captured by an open `start()`/`append()`/`prepend()` call, or
     * `null` if none is open.
     */
    private ?string $capturingBlock = null;

    /**
     * How the block currently being captured should be stored once `end()` closes it — `'assign'`, `'append'`,
     * or `'prepend'`. Meaningless while `$capturingBlock` is `null`.
     */
    private ?string $capturingMode = null;

    /**
     * The template `extend()` was last called with during the current template's own evaluation, or `null` if
     * it wasn't called at all (or was reset after being followed) — see `evaluateExtended()`.
     */
    private ?string $extendedTemplate = null;

    /**
     * @param \Elone\Core\Server\Request|null $request The current request, if any — `null` for a `View` built
     *  outside a real request, e.g. `ErrorHandler`'s own.
     */
    public function __construct(private readonly ?Request $request = null)
    {
    }

    /**
     * The current request, if this `View` was built with one — `null` otherwise.
     *
     * @return \Elone\Core\Server\Request|null
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Registers `$helper` under `$name`, making it available in templates as `$this->$name`. Only a `Helper` subclass
     * can be passed.
     *
     * @param string $name The name templates will use to access this helper — e.g. `'Html'` for `$this->Html`.
     * @param \Elone\Core\View\Helper\Helper $helper The helper instance.
     * @return void
     */
    public function loadHelper(string $name, Helper $helper): void
    {
        $this->helpers[$name] = $helper;
    }

    /**
     * Gives templates access to a helper registered via `loadHelper()`, as `$this->$name`.
     *
     * @throws \Elone\Core\Exception\HelperNotFoundException If no helper was registered under `$name`.
     */
    public function __get(string $name): Helper
    {
        if (!isset($this->helpers[$name])) {
            throw new HelperNotFoundException("Helper not loaded: `$name`.");
        }

        return $this->helpers[$name];
    }

    /**
     * Retrieves a previously `set()` value. Also, what a helper reads to see the current page's own data —
     * this only works while the template that data was `set()` for is still being evaluated, i.e. from within
     * a helper call made by that template itself; see `render()`.
     *
     * @param string $name The key to look up.
     * @param mixed $default The value to return if `$name` isn't present.
     * @return mixed The value stored under `$name`, or `$default`.
     */
    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * Merges `$data` into the values available to the next `render()` call.
     *
     * @param array<string, mixed> $data The data to make available in the rendered template.
     * @return self
     */
    public function set(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Declares that, once the current template finishes evaluating, `$templateName` should be rendered next —
     * with this template's own output (everything it didn't capture into a named block) available as the
     * `content` block, and every other block it defined by then still readable via `fetch()`.
     *
     * Calling this more than once during the same template's evaluation keeps only the last call — the
     * template that ends up being followed is whichever `$templateName` was given most recently. The template
     * being extended to can itself call `extend()` again, chaining as many steps as needed.
     *
     * @param string $templateName The template to render next, relative to `TEMPLATES`, the same way `render()`'s
     *  own `$template` argument is — without the `.php` extension.
     * @return void
     */
    public function extend(string $templateName): void
    {
        $this->extendedTemplate = $templateName;
    }

    /**
     * Starts capturing output into the named block `$name` — everything echoed until the matching `end()` call
     * is stored under that name, replacing whatever it held before. See `append()`/`prepend()` to add to a
     * block instead of replacing it, and `assign()` to set one directly without capturing anything.
     *
     * @param string $name
     * @return void
     */
    public function start(string $name): void
    {
        $this->capturingBlock = $name;
        $this->capturingMode = 'assign';
        ob_start();
    }

    /**
     * With `$value` given, appends it to the named block `$name` directly. Without it, starts capturing output
     * to append to that block instead — everything echoed until the matching `end()` call is added after
     * whatever the block already held.
     *
     * @param string $name
     * @param string|null $value
     * @return void
     */
    public function append(string $name, ?string $value = null): void
    {
        if ($value !== null) {
            $this->blocks[$name] = ($this->blocks[$name] ?? '') . $value;

            return;
        }

        $this->capturingBlock = $name;
        $this->capturingMode = 'append';
        ob_start();
    }

    /**
     * With `$value` given, prepends it to the named block `$name` directly. Without it, starts capturing output
     * to prepend to that block instead — everything echoed until the matching `end()` call is added before
     * whatever the block already held.
     *
     * @param string $name
     * @param string|null $value
     * @return void
     */
    public function prepend(string $name, ?string $value = null): void
    {
        if ($value !== null) {
            $this->blocks[$name] = $value . ($this->blocks[$name] ?? '');

            return;
        }

        $this->capturingBlock = $name;
        $this->capturingMode = 'prepend';
        ob_start();
    }

    /**
     * Directly sets the named block `$name` to `$value`, discarding whatever it held before. Useful for turning
     * an already-available string (a view variable, say) into a block, without needing to capture anything.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function assign(string $name, string $value): void
    {
        $this->blocks[$name] = $value;
    }

    /**
     * Clears the named block `$name` entirely, as if it had never been set.
     *
     * @param string $name
     * @return void
     */
    public function reset(string $name): void
    {
        unset($this->blocks[$name]);
    }

    /**
     * Closes whichever `start()`, `append()`, or `prepend()` call is currently capturing, storing its buffered
     * output into that block the way that call asked for — replacing, appending, or prepending.
     *
     * @return void
     * @throws \LogicException If no `start()`, `append()`, or `prepend()` call is currently open to close.
     */
    public function end(): void
    {
        if ($this->capturingBlock === null) {
            throw new LogicException('end() called with no matching start(), append(), or prepend() open.');
        }

        $captured = ob_get_clean() ?: '';
        $name = $this->capturingBlock;

        $this->blocks[$name] = match ($this->capturingMode) {
            'append' => ($this->blocks[$name] ?? '') . $captured,
            'prepend' => $captured . ($this->blocks[$name] ?? ''),
            default => $captured,
        };

        $this->capturingBlock = null;
        $this->capturingMode = null;
    }

    /**
     * The named block `$name`'s own content, or `$default` if it was never set (by `start()`/`end()`,
     * `assign()`, or `append()`/`prepend()`).
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function fetch(string $name, string $default = ''): string
    {
        return $this->blocks[$name] ?? $default;
    }

    /**
     * Renders a reusable snippet of markup from `templates/element/`, meant to be included from within another
     * template.
     *
     * Example:
     * ```
     * <?= $this->element('user-card', ['user' => $user]) ?>
     * ```
     *
     * Unlike `render()`, an element never gets wrapped in a layout, and it only sees the data explicitly passed to
     * it — nothing already `set()` on the view.
     *
     * @param string $name The name of the element to render, relative to `templates/element/` (without extension).
     * @param array<string, mixed> $data Data to extract and make available within the element's scope.
     * @return string The rendered content of the element.
     * @throws \Elone\Core\Exception\TemplateNotFoundException If `$name` doesn't resolve to an existing file.
     * @throws \Throwable Whatever the element itself throws while rendering — re-thrown as-is.
     */
    public function element(string $name, array $data = []): string
    {
        $elementFile = $this->resolve(TEMPLATES . '/element', $name);
        if ($elementFile === false) {
            throw new TemplateNotFoundException("Element not found: `$name.php`.");
        }

        return $this->evaluate($elementFile, $data);
    }

    /**
     * Renders `$template` and, unless `$layout` is `null`, wraps the result in the given layout. If `$template`
     * (or, in turn, `$layout`) calls `extend()`, follows the chain until a file that doesn't call it produces
     * the final content — see `extend()`.
     *
     * `$this->data` (everything `set()` before this call) stays available — via `get()` — for the whole
     * duration of the template's own evaluation, so a helper the template calls can see it too; it's only
     * cleared once the template itself has finished. The layout never reads `$this->data` directly either way —
     * it gets the same values explicitly, as a local copy, alongside `content`. Every block defined anywhere
     * along the way — by the template, or by any template it `extend()`s to — is cleared once this call
     * returns, the same way `$this->data` is.
     *
     * @param string $template The name of the template to render, relative to `TEMPLATES` (without extension).
     * @param string|null $layout The name of the layout to wrap the content in. Defaults to `'default'`. Pass
     *  `null` to render the template with no layout at all.
     * @return string The rendered content.
     * @throws \Elone\Core\Exception\TemplateNotFoundException If `$template`, or a template it `extend()`s to,
     *  doesn't resolve to an existing file.
     * @throws \Elone\Core\Exception\LayoutNotFoundException If `$layout`, or a layout it `extend()`s to, doesn't
     *  resolve to an existing file.
     * @throws \Throwable Whatever the template (or layout) itself throws while rendering — re-thrown as-is.
     */
    public function render(string $template, ?string $layout = 'default'): string
    {
        $templateFile = $this->resolve(TEMPLATES, $template);
        if ($templateFile === false) {
            throw new TemplateNotFoundException("Template not found: `$template.php`.");
        }

        $data = $this->data;

        try {
            $content = $this->evaluateExtended($templateFile, $data);
        } finally {
            $this->data = [];
        }

        if ($layout === null) {
            $this->blocks = [];

            return $content;
        }

        try {
            return $this->renderLayout($content, $data, $layout);
        } finally {
            $this->blocks = [];
        }
    }

    /**
     * Wraps `$content` in `$layout`.
     *
     * @param array<string, mixed> $data Data to extract and make available within the layout's scope, alongside
     *  `$content`. If `$data` happens to have its own `content` key, it's overridden by the real `$content` —
     *  the layout should never see anything else under that name.
     * @throws \Elone\Core\Exception\LayoutNotFoundException If `$layout` doesn't resolve to an existing file.
     * @throws \Throwable Whatever the layout itself throws while rendering — re-thrown as-is.
     */
    private function renderLayout(string $content, array $data, string $layout): string
    {
        $layoutFile = $this->resolve(TEMPLATES . '/layout', $layout);
        if ($layoutFile === false) {
            throw new LayoutNotFoundException("Layout not found: `$layout.php`.");
        }

        $this->blocks['content'] = $content;

        return $this->evaluateExtended($layoutFile, [...$data, 'content' => $content]);
    }

    /**
     * Evaluates `$file`, then follows whatever `extend()` chain it triggers: each step's own output becomes the
     * next step's `content` — both as a plain `$content` variable and as the `content` block, so a template
     * being extended to can use either `<?= $content ?>` or `$this->fetch('content')` — until a file that
     * doesn't call `extend()` produces the final result.
     *
     * @param array<string, mixed> $data
     * @throws \Elone\Core\Exception\TemplateNotFoundException If a template named via `extend()` doesn't
     *  resolve to an existing file.
     * @throws \Throwable Whatever the file itself throws while rendering — re-thrown as-is.
     */
    private function evaluateExtended(string $file, array $data): string
    {
        $content = $this->evaluate($file, $data);

        if ($this->extendedTemplate === null) {
            return $content;
        }

        $this->blocks['content'] = $content;

        $nextTemplateName = $this->extendedTemplate;
        $this->extendedTemplate = null;

        $nextFile = $this->resolve(TEMPLATES, $nextTemplateName);
        if ($nextFile === false) {
            throw new TemplateNotFoundException("Extended template not found: `$nextTemplateName.php`.");
        }

        return $this->evaluateExtended($nextFile, [...$data, 'content' => $content]);
    }

    /**
     * Extracts `$__data` and requires `$__file`, buffering and returning everything it echoes. Isolated in its
     * own method so the only locals in scope during `extract()` are these two parameters — otherwise `EXTR_SKIP`
     * would silently drop a data key that happens to match a variable already in scope.
     *
     * @param array<string, mixed> $__data
     * @throws \Throwable Whatever `require`-ing `$__file` throws — re-thrown as-is, after releasing the buffer.
     */
    private function evaluate(string $__file, array $__data): string
    {
        extract($__data, EXTR_SKIP);

        ob_start();

        try {
            require $__file;
            $content = ob_get_clean() ?: '';
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        return $content;
    }

    /**
     * Resolves `$name` to a real file path inside `$basePath`, refusing anything that would resolve outside it —
     * traversal or a symlink escaping the directory. Returns false when the file doesn't exist, isn't a regular
     * file, or escapes the base directory.
     *
     * @param string $basePath The base directory where the file is expected to be located.
     * @param string $name The name of the file to resolve, without the .php extension.
     * @return string|false The full path to the resolved file if it exists and is valid or `false`.
     */
    protected function resolve(string $basePath, string $name): string|false
    {
        $realBasePath = realpath($basePath);
        if ($realBasePath === false) {
            return false;
        }

        $file = realpath("$realBasePath/$name.php");
        if ($file === false || !is_file($file) || !str_starts_with($file, $realBasePath . DS)) {
            return false;
        }

        return $file;
    }
}
