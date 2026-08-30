<?php
declare(strict_types=1);

namespace Elone\Core;

use Closure;
use Elone\Core\Exception\HttpException;
use Elone\Core\Server\Response;
use Elone\Core\View\View;
use Throwable;

final class ErrorHandler
{
    private readonly View $view;

    private readonly Closure $logger;

    /**
     * @param \Elone\Core\Configuration $configuration
     * @param (\Closure(string): void)|null $logger Called with the string representation of an exception whenever
     *  `debug` is off. Defaults to PHP's `error_log()`. Inject a no-op (or a spy) in tests to avoid writing to the
     *  real error log.
     */
    public function __construct(private readonly Configuration $configuration, ?Closure $logger = null)
    {
        $this->view = new View($configuration);
        $this->logger = $logger ?? error_log(...);
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
            ($this->logger)((string)$exception);
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
