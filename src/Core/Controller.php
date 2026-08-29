<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\View\View;

/**
 * Represents the base controller class responsible for managing the interaction between the view and the data provided.
 */
abstract class Controller
{
    public function __construct(protected readonly View $view)
    {
    }

    /**
     * Sets the provided data into the view.
     *
     * @param array<string, mixed> $data The data to be set in the view.
     * @return void
     */
    protected function set(array $data): void
    {
        $this->view->set($data);
    }
}
