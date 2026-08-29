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

            // `Application` takes the router result and passes it to the dispatcher
            $response = $this->dispatcher->dispatch(
                $route['controller'],
                $route['action'],
                $route['params'],
            );
        } catch (Throwable $exception) {
            $response = $this->errorHandler->handle($exception);
        }

        $response->send();
    }
}
