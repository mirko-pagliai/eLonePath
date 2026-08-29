<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\View\View;

/**
 * Represents the base controller class responsible for managing the interaction between the view and the data provided.
 */
abstract class Controller
{
    protected readonly View $view;

    public function __construct(?View $view = null)
    {
        $this->view = $view ?? new View();
    }

    /**
     * Renders the specified template with an optional layout.
     *
     * @param string $template The name of the template to be rendered.
     * @param string|null $layout The name of the layout to apply. Defaults to 'default'.
     * @return string The rendered output as a string.
     */
    public function render(string $template, ?string $layout = 'default'): string
    {
        return $this->view->render($template, $layout);
    }

    /**
     * Sets the provided data into the view.
     *
     * @param array<string, mixed> $data
     * @return void
     */
    protected function set(array $data): void
    {
        $this->view->set($data);
    }
}
