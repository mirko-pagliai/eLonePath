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
     * Restricted to `POST` via `allowMethod()`, the CakePHP idiom — used to verify it works when called from a
     * real dispatched action, not just directly on `Request`/`Controller` in isolation.
     *
     * @return \Elone\Core\Server\Response
     * @throws \Elone\Core\Exception\MethodNotAllowedException If the request isn't a `POST`.
     */
    public function postOnly(): Response
    {
        $this->allowMethod('post');

        return new Response('post handled', 200);
    }

    /**
     * Reports which HTTP method it was called with, via `is()` — used to verify it works when called from a real
     * dispatched action.
     *
     * @return \Elone\Core\Server\Response
     */
    public function checkMethod(): Response
    {
        return new Response($this->is('post') ? 'post' : ($this->is('get') ? 'get' : 'other'), 200);
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
