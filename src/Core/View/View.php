<?php
declare(strict_types=1);

namespace App\Core\View;

use RuntimeException;

final class View
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    public function __construct(private readonly string $templatesPath)
    {
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
        $templateFile = $this->templatesPath . '/' . $template . '.php';

        if (!is_file($templateFile)) {
            throw new RuntimeException("Template not found: {$template}");
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
        $layoutFile = $this->templatesPath . '/layout/' . $layout . '.php';

        if (!is_file($layoutFile)) {
            throw new RuntimeException("Layout not found: {$layout}");
        }

        ob_start();

        extract($data, EXTR_SKIP);

        require $layoutFile;

        return ob_get_clean() ?: '';
    }
}
