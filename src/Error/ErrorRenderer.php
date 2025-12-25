<?php
declare(strict_types=1);

namespace eLonePath\Error;

use eLonePath\View\ErrorView;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Handles the rendering of error pages for HTTP exceptions and errors.
 *
 * This class is responsible for:
 *
 * - Logging errors to the console (`error_log()`)
 * - Rendering appropriate error templates (`400.php` or `500.php`)
 * - Creating HTTP Response objects with correct status codes
 */
class ErrorRenderer
{
    /**
     * Renders an error page and returns an HTTP Response.
     *
     * This method:
     * 1. Logs the error details to the console (if an exception is provided)
     * 2. Determines the appropriate template based on status code
     * 3. Renders the error page using ErrorView
     * 4. Returns a Response object with the rendered content
     *
     * @param int $statusCode The HTTP status code (e.g., 404, 500)
     * @param \Throwable|null $exception The exception that caused the error, if any
     * @return \Symfony\Component\HttpFoundation\Response The HTTP response containing the rendered error page
     */
    public function render(int $statusCode, ?Throwable $exception = null): Response
    {
        $this->logToConsole($statusCode, $exception);

        $view = new ErrorView();
        $content = $view->renderError($statusCode, $exception);

        return new Response($content, $statusCode);
    }

    /**
     * Logs error information to the console using `error_log()`.
     *
     * Logs both the error message with file/line information and the full stack trace.
     * This runs regardless of `DEBUG` mode - console logging is always enabled.
     *
     * @param int $statusCode The HTTP status code
     * @param \Throwable|null $exception The exception to log, if any
     * @return void
     */
    private function logToConsole(int $statusCode, ?Throwable $exception): void
    {
        if ($exception === null) {
            return;
        }

        $message = sprintf(
            '[%d] %s in %s:%d',
            $statusCode,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );

        $trace = $exception->getTraceAsString();

        // Write to error_log (production)
        error_log($message);
        error_log($trace);

        // Write to STDERR (development - visible in terminal)
        file_put_contents('php://stderr', $message . "\n");
        file_put_contents('php://stderr', $trace . "\n\n");
    }
}
