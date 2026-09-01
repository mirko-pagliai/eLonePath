<?php
declare(strict_types=1);

namespace Elone\Core\View;

use Elone\Core\Exception\LayoutNotFoundException;
use Elone\Core\Exception\TemplateNotFoundException;
use Elone\Core\View\Helper\HtmlHelper;
use Throwable;

class View
{
    public readonly HtmlHelper $Html;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct()
    {
        $this->Html = new HtmlHelper();
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->data[$name] ?? $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function set(array $data): self
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Renders a reusable snippet of markup from `templates/element/`, meant to be included from within another
     * template.
     *
     * Example:
     * ```
     * <?= $this->element('story-title', ['game' => $game]) ?>
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
     * Renders `$template` and, unless `$layout` is `null`, wraps the result in the given layout.
     *
     * @param string $template The name of the template to render, relative to `TEMPLATES` (without extension).
     * @param string|null $layout The name of the layout to wrap the content in. Defaults to `'default'`. Pass
     *  `null` to render the template with no layout at all.
     * @return string The rendered content.
     * @throws \Elone\Core\Exception\TemplateNotFoundException If `$template` doesn't resolve to an existing file.
     * @throws \Elone\Core\Exception\LayoutNotFoundException If `$layout` doesn't resolve to an existing file.
     * @throws \Throwable Whatever the template (or layout) itself throws while rendering — re-thrown as-is.
     */
    public function render(string $template, ?string $layout = 'default'): string
    {
        $templateFile = $this->resolve(TEMPLATES, $template);
        if ($templateFile === false) {
            throw new TemplateNotFoundException("Template not found: `$template.php`.");
        }

        $data = $this->data;
        $this->data = [];

        $content = $this->evaluate($templateFile, $data);
        if ($layout === null) {
            return $content;
        }

        return $this->renderLayout($content, $data, $layout);
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

        return $this->evaluate($layoutFile, [...$data, 'content' => $content]);
    }

    /**
     * Extracts `$__data` into a local scope and requires `$__file`, buffering and returning everything it echoes.
     *
     * Kept in its own method — rather than inline in `render()`, `element()`, and `renderLayout()` — specifically
     * so the only local variables in scope when `extract()` runs are its own two parameters. `EXTR_SKIP` refuses
     * to overwrite a variable that already exists in scope, so reusing a common name here (like `$data` or
     * `$file`) would silently drop any view variable with that same key — which is exactly what happened before
     * this method existed, with `element()`'s own `$name` parameter swallowing a data key also called `name`.
     * The double-underscore names below aren't pretty, but nothing a template author would plausibly choose as a
     * data key collides with them.
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
