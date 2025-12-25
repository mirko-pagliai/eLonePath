<?php
declare(strict_types=1);

namespace eLonePath\View;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simple view rendering class.
 */
class View
{
    private string $templatePath;

    private string $layout = 'default';

    public function __construct(?string $templatePath = null)
    {
        $this->templatePath = $templatePath ?? ROOT . DS . 'templates';
    }

    /**
     * Render a template with a layout
     *
     * @param string $template Template file path (e.g., 'home/index')
     * @param array $data Data to pass to the template
     * @param string|null $layout Layout to use (null for no layout)
     * @return Response
     */
    public function render(string $template, array $data = [], ?string $layout = null): Response
    {
        // Use specified layout or default
        $layout = $layout ?? $this->layout;

        // Render the template content
        $content = $this->renderTemplate($template, $data);

        // If the layout is specified, wrap content in the layout
        if ($layout !== null) {
            $data['content'] = $content;
            $html = $this->renderTemplate("layouts/{$layout}", $data);
        } else {
            $html = $content;
        }

        return new Response($html);
    }

    /**
     * Render a template file
     *
     * @param string $template Template file path
     * @param array $data Data to extract as variables
     * @return string
     * @throws \RuntimeException If a template file does not exist
     */
    private function renderTemplate(string $template, array $data): string
    {
        $templateFile = $this->templatePath . '/' . $template . '.php';

        if (!file_exists($templateFile)) {
            throw new RuntimeException("Template not found: {$templateFile}");
        }

        // Extract data as variables
        extract($data, EXTR_SKIP);

        // Start output buffering
        ob_start();

        // Include template
        include $templateFile;

        // Return buffered content
        return ob_get_clean();
    }

    /**
     * Set the default layout
     *
     * @param string|null $layout
     * @return self
     */
    public function setLayout(?string $layout): self
    {
        $this->layout = $layout;

        return $this;
    }
}
