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

        return $this->renderLayout($content, $data, $layout);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function renderLayout(
        string $content,
        array $data,
        string $layout,
    ): string {
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
    protected function resolve(string $basePath, string $name): string|false
    {
        $realBasePath = realpath($basePath);
        if ($realBasePath === false) {
            return false;
        }

        $file = realpath("$realBasePath/$name.php");

        if ($file === false || !is_file($file) || !str_starts_with($file, $realBasePath . DIRECTORY_SEPARATOR)) {
            return false;
        }

        return $file;
    }
}
