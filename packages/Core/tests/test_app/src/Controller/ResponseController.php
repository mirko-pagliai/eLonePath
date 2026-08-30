<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;
use Elone\Core\Server\Response;

/**
 * An action that returns a `Response` directly, bypassing the view layer entirely — used to test `Dispatcher` and
 * `Application` without depending on template fixtures.
 */
class ResponseController extends Controller
{
    public function ok(): Response
    {
        return new Response('Hello from ResponseController.', 200);
    }
}
