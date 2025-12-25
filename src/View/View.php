<?php
declare(strict_types=1);

namespace eLonePath\View;

use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

/**
 * The `View` class provides functionality to render templates with optional layout support.
 *
 * It manages template-related data, integrates request handling, and supports auto-detecting templates
 * based on the current request.
 */
class View
{
    private string $templatePath;

    protected ?string $layout;

    /** @var array<string, mixed> */
    protected array $data = [];

    private ?Request $request = null;

    /**
     * Constructor method.
     *
     * @param string|null $templatePath The path to the templates' directory. Defaults to `TEMPLATES` constant if not provided.
     * @param string|null $layout The default layout file or null for no layout. Defaults to 'layouts/default.php'.
     * @return void
     * @throws \InvalidArgumentException If the provided template path does not exist.
     */
    public function __construct(
        ?string $templatePath = null,
        ?string $layout = 'layouts/default.php'
    ) {
        $templatePath = rtrim($templatePath ?: TEMPLATES, DS);

        if (!is_dir($templatePath)) {
            throw new InvalidArgumentException("Template path does not exist: `{$templatePath}`.");
        }

        $this->templatePath = $templatePath;
        $this->layout = $layout;
    }

    /**
     * Sets the layout template to be used when rendering.
     *
     * @param string|null $layout The layout file path relative to templatePath, or null for no layout.
     * @return void
     */
    public function setLayout(?string $layout): void
    {
        $this->layout = $layout;
    }

    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }

    /**
     * Stores the provided data array into the internal storage.
     * Throws an exception if a key in the provided data already exists in the internal storage.
     *
     * @param array<string, mixed> $data An associative array where the keys and values will be added to the internal storage.
     * @return void
     *
     * @throws \InvalidArgumentException If a key in the input array already exists in the storage.
     */
    public function set(array $data): void
    {
        foreach ($data as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                throw new InvalidArgumentException("Data key `{$key}` already exists.");
            }
            $this->data[$key] = $value;
        }
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

    /**
     * Automatically detects the template path based on the controller and action in the current request.
     *
     * @return string The resolved template path in the format `ControllerName/action_name.php`.
     * @throws \InvalidArgumentException If the request is not set, or if the controller cannot be determined from the request attributes.
     */
    protected function autoDetectTemplate(): string
    {
        if ($this->request === null) {
            throw new InvalidArgumentException('Request not set. Call `setRequest()` before `render()`.');
        }

        /** @var array{class-string, non-empty-string}|non-empty-string $controller */
        $controller = $this->request->attributes->get('_controller');
        if (!$controller) {
            throw new InvalidArgumentException('Controller not found in request attributes.');
        }

        /**
         * Handles both:
         *
         *  - Controller as an array `[Namespace\Controller\ClassName::class, 'methodName']`;
         *  - Controller as a string `'Namespace\Controller\ClassName::methodName'`.
         */
        if (is_array($controller)) {
            [$class, $action] = $controller;
        } else {
            [$class, $action] = explode('::', $controller);
        }

        // Extract controller name (remove namespace and "Controller" suffix)
        $controllerName = basename(str_replace('\\', '/', $class));
        $controllerName = str_replace('Controller', '', $controllerName);

        // Convert action to `snake_case`
        $actionName = $this->camelToSnake($action);

        return "{$controllerName}/{$actionName}.php";
    }

    /**
     * Converts a `camelCase` string to `snake_case` format.
     *
     * @param string $input The `camelCase` string to be converted.
     * @return string The resulting `snake_case` string.
     */
    protected function camelToSnake(string $input): string
    {
        $result = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $input) ?: '';
        $result = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $result) ?: '';

        return strtolower($result);
    }

    /**
     * Renders a template file with the given data and returns the result as a string.
     *
     * @param string $file The name of the template file to render.
     * @param array<string, mixed> $data An associative array of data to be extracted and made available within the template file.
     * @return string The rendered content of the template file.
     * @throws \InvalidArgumentException If the template file does not exist, or if the output from the template file is invalid.
     */
    protected function renderFile(string $file, array $data): string
    {
        $filePath = $this->templatePath . '/' . $file;

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Template file not found: `{$filePath}`.");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $filePath;
        $result = ob_get_clean();

        if (!is_string($result)) {
            throw new InvalidArgumentException("Template file returned invalid output: `{$filePath}`.");
        }

        return $result;
    }
}