<?php
declare(strict_types=1);

namespace Elone\Core\View;

use Elone\Core\Configuration;
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

    public function __construct(private readonly Configuration $configuration)
    {
        $this->Html = new HtmlHelper($configuration);
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
        $this->assertSafeName($template);

        $templateFile = $this->configuration->templatesPath() . "/$template.php";

        if (!is_file($templateFile)) {
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
        $this->assertSafeName($layout);

        $layoutFile = $this->configuration->templatesPath() . "/layout/$layout.php";

        if (!is_file($layoutFile)) {
            throw new LayoutNotFoundException("Layout not found: `$layout`.");
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
     * Rejects template/layout names that could escape the templates' directory.
     */
    private function assertSafeName(string $name): void
    {
        if (!preg_match('/^[a-zA-Z0-9_\-\/]+$/', $name)) {
            throw new TemplateNotFoundException("Invalid template name: `$name`.");
        }
    }
}
