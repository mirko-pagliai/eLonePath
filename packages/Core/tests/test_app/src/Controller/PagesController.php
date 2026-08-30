<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;

class PagesController extends Controller
{
    public function home(): void
    {
    }

    public function view(int $id): void
    {
    }

    /**
     * This is a bad action (is not public), useful for testing purposes only.
     *
     * @return void
     */
    protected function invalidAction(): void
    {
    }
}
