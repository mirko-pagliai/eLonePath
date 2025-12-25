<?php
declare(strict_types=1);

namespace eLonePath\View;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class View
{
    private string $templatePath;

    private ?string $layout = null;

    private array $data = [];

    private ?Request $request = null;

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

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    public function set(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    public function render(?string $template = null): string
    {
        // If no template specified, auto-detect from request
        if ($template === null) {
            $template = $this->autoDetectTemplate();
        }

        // Render template
        $content = $this->renderFile($template, $this->data);

        // If the layout is set, render content inside the layout
        if ($this->layout !== null) {
            $this->data['content'] = $content;

            return $this->renderFile($this->layout, $this->data);
        }

        return $content;
    }

    private function autoDetectTemplate(): string
    {
        if ($this->request === null) {
            throw new InvalidArgumentException('Request not set. Call setRequest() before render()');
        }

        /** @var array{class-string, non-empty-string}|non-empty-string $controller */
        $controller = $this->request->attributes->get('_controller');
        if (!$controller) {
            throw new InvalidArgumentException('Controller not found in request attributes');
        }

        /**
         * Handles both:
         *
         *  - controller as an array `[Namespace\Controller\ClassName::class, 'methodName']`;
         *  - controller as a string `'Namespace\Controller\ClassName::methodName'`.
         */
        if (is_array($controller)) {
            [$class, $action] = $controller;
        } else {
            [$class, $action] = explode('::', $controller);
        }

        // Extract controller name (remove namespace and "Controller" suffix)
        $controllerName = basename(str_replace('\\', '/', $class));
        $controllerName = str_replace('Controller', '', $controllerName);

        // Convert action to snake_case
        $actionName = $this->camelToSnake($action);

        return "{$controllerName}/{$actionName}.php";
    }

    private function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
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
