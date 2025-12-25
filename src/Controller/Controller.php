<?php
declare(strict_types=1);

namespace eLonePath\Controller;

use eLonePath\View\View;

/**
 * Abstract base class for managing controllers in an application.
 *
 * Provides methods to initialize a view instance, set data to it,
 * and render the resulting content.
 */
abstract class Controller
{
    protected(set) View $view;

    /**
     * Class constructor.
     *
     * Initializes the view instance and sets its default layout.
     *
     * @return void
     */
    public function __construct()
    {
        $this->view = new View();
        $this->view->setLayout('layouts/default.php');
    }

    /**
     * Sets the given data to the view.
     *
     * @param array<string, mixed> $data An associative array containing data to be set.
     * @return void
     */
    public function set(array $data): void
    {
        $this->view->set($data);
    }

    /**
     * Renders the view and returns the rendered content as a string.
     *
     * @return string The rendered content.
     */
    public function render(): string
    {
        return $this->view->render();
    }
}
