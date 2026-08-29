<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\View\View;

abstract class Controller
{
    public function __construct(protected readonly View $view)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function set(array $data): void
    {
        $this->view->set($data);
    }
}
