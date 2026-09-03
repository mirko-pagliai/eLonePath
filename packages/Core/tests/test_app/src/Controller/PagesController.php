<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;
use Elone\Core\Server\Response;

class PagesController extends Controller
{
    public function home(): void
    {
    }

    public function view(int $id): void
    {
    }

    /**
     * An action that returns a `Response` directly, bypassing the view layer entirely — used to test `Dispatcher` and
     * `Application` without depending on template fixtures.
     *
     * @return \Elone\Core\Server\Response
     */
    public function printResponse(): Response
    {
        return new Response('Hello from `' . __METHOD__ . '()`.', 200);
    }

    /**
     * This is a bad (protected) action, useful for testing purposes only.
     *
     * @return void
     */
    protected function invalidAction(): void
    {
    }
}
