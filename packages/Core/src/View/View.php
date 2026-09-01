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
     * <?= $this->element('story-title', ['game' => $game]) ?>`
     * ```
     *
     * Unlike `render()`, an element never gets wrapped in a layout, and it only sees the data explicitly passed to it
     * — nothing already `set()` on the view.
     *
     * @param string $name The name of the element to render, corresponding to the file name (without extension).
     * @param array<string, mixed> $data An associative array of data to extract and make available within the element's scope.
     *
     * @return string The rendered content of the element as a string.
     *
     * @throws \Elone\Core\Exception\TemplateNotFoundException If the specified element file does not exist.
     * @throws \Throwable If an error occurs during the rendering process.
     */
    public function element(string $name, array $data = []): string
    {
        $elementFile = $this->resolve(TEMPLATES . '/element', $name);
        if ($elementFile === false) {
            throw new TemplateNotFoundException("Element not found: `$name.php`.");
        }

        ob_start();

        try {
            extract($data, EXTR_SKIP);
            require $elementFile;
            $content = ob_get_clean() ?: '';
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        return $content;
    }

    /**
     * Renders a given template and optionally applies a layout to the rendered content.
     *
     * @param string $template The name of the template to render.
     * @param string|null $layout The name of the layout to apply to the rendered template. Defaults to `default`. If
     * `null` is provided, no layout will be applied.
     * @return string The fully rendered content, with or without the layout applied, depending on the parameters.
     * @throws \Elone\Core\Exception\TemplateNotFoundException If the specified template file cannot be found.
     * @throws \Throwable If an error occurs during template rendering.
     */
    public function render(string $template, ?string $layout = 'default'): string
    {
        $templateFile = $this->resolve(TEMPLATES, $template);
        if ($templateFile === false) {
            throw new TemplateNotFoundException("Template not found: `$template.php`.");
        }

        $data = $this->data;
        $this->data = [];

        // Always release the buffer, even on failure
        ob_start();

        try {
            extract($data, EXTR_SKIP);
            require $templateFile;
            $content = ob_get_clean() ?: '';
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        if ($layout === null) {
            return $content;
        }

        return $this->renderLayout(content: $content, data: $data, layout: $layout);
    }

    /**
     * Renders a layout by wrapping the provided content with a specified layout file located in the templates'
     * directory. The layout receives the provided data for rendering.
     *
     * @param string $content The main content to be embedded within the layout.
     * @param array<string, mixed> $data Key-value pairs of data to be extracted and made available in the layout scope.
     * @param string $layout The name of the layout file to be used for rendering.
     * @return string The fully rendered layout, including the provided content.
     * @throws \Elone\Core\Exception\LayoutNotFoundException If the specified layout file cannot be found.
     * @throws \Throwable If an exception occurs during layout rendering.
     */
    private function renderLayout(string $content, array $data, string $layout): string
    {
        $layoutFile = $this->resolve(TEMPLATES . '/layout', $layout);
        if ($layoutFile === false) {
            throw new LayoutNotFoundException("Layout not found: `$layout.php`.");
        }

        // Always release the buffer, even on failure
        ob_start();

        try {
            extract($data, EXTR_SKIP);
            require $layoutFile;

            $content = ob_get_clean() ?: '';
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        return $content;
    }

    /**
     * Resolves `$name` to a real file path inside `$basePath`, refusing anything that would resolve outside it —
     * traversal or a symlink escaping the directory. Returns false when the file doesn't exist, isn't a regular file,
     * or escapes the base directory.
     */
    /**
     * Resolves the full path of a specified file within a given base directory.
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
