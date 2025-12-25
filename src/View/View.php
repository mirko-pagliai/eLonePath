<?php
declare(strict_types=1);

namespace eLonePath\View;

use InvalidArgumentException;

class View
{
    private string $templatePath;

    private ?string $layout = null;

    private array $data = [];

    public function __construct(?string $templatePath = null)
    {
        $templatePath = rtrim($templatePath ?: TEMPLATES, DS);

        if (!is_dir($templatePath)) {
            throw new InvalidArgumentException("Template path does not exist: `{$templatePath}`.");
        }

        $this->templatePath = $templatePath;
    }

    public function setLayout(string $layout): void
    {
        $this->layout = $layout;
    }

    public function set(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function render(string $template): string
    {
        // Render template
        $content = $this->renderFile($template, $this->data);

        // If the layout is set, render content inside the layout
        if ($this->layout !== null) {
            $this->data['content'] = $content;

            return $this->renderFile($this->layout, $this->data);
        }

        return $content;
    }

    private function renderFile(string $file, array $data): string
    {
        $filePath = $this->templatePath . '/' . $file;

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Template file not found: {$filePath}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $filePath;

        return ob_get_clean();
    }
}
