<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    public function __construct(protected readonly View $view)
    {
    }

    protected function set(array $data): void
    {
        $this->view->set($data);
    }
}
