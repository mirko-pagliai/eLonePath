<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Exception\HttpException;
use App\Core\Server\Response;
use App\Core\View\View;
use Throwable;

final class ErrorHandler
{
    private readonly View $view;

    public function __construct(private readonly bool $debug = false)
    {
        $this->view = new View();
    }

    public function handle(Throwable $exception): Response
    {
        if ($exception instanceof HttpException) {
            $status = $exception->statusCode();
            $message = $exception->getMessage();
        } else {
            $status = 500;
            $message = $this->debug ? $exception->getMessage() : 'Internal Server Error';
        }

        $this->view->set([
            'debug' => $this->debug,
        ] + compact('status', 'message', 'exception'));

        $content = $this->view->render("error/$status", 'error');

        return new Response($content, $status);
    }
}
