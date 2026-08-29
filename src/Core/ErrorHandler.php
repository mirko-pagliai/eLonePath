<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ErrorHandler
{
    public function __construct(private readonly View $view, private readonly bool $debug = false)
    {
    }

    public function handle(Throwable $exception): Response
    {
        if ($exception instanceof HttpException) {
            $status = $exception->statusCode();
            $message = $exception->getMessage();
        } else {
            $status = 500;
            $message = $this->debug
                ? $exception->getMessage()
                : 'Internal Server Error';
        }

        $this->view->set([
            'status' => $status,
            'message' => $message,
            'exception' => $exception,
            'debug' => $this->debug,
        ]);

        $content = $this->view->render(
            "error/{$status}",
            'error',
        );

        return new Response($content, $status);
    }
}
