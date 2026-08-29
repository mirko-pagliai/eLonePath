<?php
declare(strict_types=1);

namespace App\Core\View;

use App\Core\Exception\TemplateNotFoundException;
use App\Core\Router;
use App\Core\View\Helper\HtmlHelper;
use RuntimeException;

final class View
{
    public readonly HtmlHelper $Html;

    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly string $templatesPath, Router $router)
    {
        $this->Html = new HtmlHelper($router);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function set(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function render(string $template, ?string $layout = 'default'): string
    {
        $templateFile = $this->templatesPath . "/$template.php";

        if (!is_file($templateFile)) {
            throw new TemplateNotFoundException("Template not found: `$template.php`.");
        }

        $data = $this->data;
        $this->data = [];

        ob_start();

        extract($data, EXTR_SKIP);

        require $templateFile;

        $content = ob_get_clean() ?: '';

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
        $layoutFile = $this->templatesPath . "/layout/$layout.php";

        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout not found: $layout");
        }

        ob_start();

        extract($data, EXTR_SKIP);

        require $layoutFile;

        return ob_get_clean() ?: '';
    }
}
