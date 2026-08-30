<?php
declare(strict_types=1);

namespace Elone\Core;

use Elone\Core\Exception\HttpException;
use Elone\Core\Server\Response;
use Elone\Core\View\View;
use Throwable;

final class ErrorHandler
{
    private readonly View $view;

    public function __construct(private readonly Configuration $configuration)
    {
        $this->view = new View($configuration);
    }

    public function handle(Throwable $exception): Response
    {
        if ($exception instanceof HttpException) {
            $status = $exception->statusCode();
            $message = $exception->getMessage();
        } else {
            $status = 500;
            $message = $this->configuration->debug() ? $exception->getMessage() : 'Internal Server Error';
        }

        if (!$this->configuration->debug()) {
            error_log((string)$exception);
        }

        $this->view->set([
            'debug' => $this->configuration->debug(),
            'status' => $status,
            'message' => $message,
            'exception' => $exception,
        ]);

        $template = intdiv($status, 100) === 4 ? 'error/400' : 'error/500';

        try {
            $content = $this->view->render($template, 'error');
        } catch (Throwable) {
            /**
             * The error template itself failed to render: fall back to a bare response instead of letting the exception
             * escape uncaught.
             */
            return new Response('Internal Server Error', 500);
        }

        return new Response($content, $status);
    }
}
