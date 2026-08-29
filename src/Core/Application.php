<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Server\Request;
use Throwable;

final readonly class Application
{
    public function __construct(
        private Router $router,
        private Dispatcher $dispatcher,
        private ErrorHandler $errorHandler,
    ) {
    }

    public function run(): void
    {
        $request = Request::capture();

        try {
            $route = $this->router->dispatch($request);

            $response = $this->dispatcher->dispatch($route);
        } catch (Throwable $exception) {
            $response = $this->errorHandler->handle($exception);
        }

        $response->send();
    }
}
