<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\Routing\Router;
use Elone\Core\Server\Request;
use Throwable;

final readonly class Application
{
    public function __construct(
        private Router $router,
        private Dispatcher $dispatcher,
        private ErrorHandler $errorHandler,
    ) {
    }

    /**
     * Handles the execution flow of the application by capturing the incoming request, dispatching the appropriate
     * route, and sending the corresponding response.
     *
     * If an error occurs during the process, it is handled by the error handler.
     *
     * @return void
     */
    public function run(): void
    {
        $request = Request::capture();

        try {
            $route = $this->router->dispatch(request: $request);

            $response = $this->dispatcher->dispatch(route: $route, request: $request);
        } catch (Throwable $exception) {
            $response = $this->errorHandler->handle(exception: $exception);
        }

        $response->send();
    }
}
