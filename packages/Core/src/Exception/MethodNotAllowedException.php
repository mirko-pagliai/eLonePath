<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown by `Request::allowMethod()` when the current request's HTTP method isn't one of the methods
 * an action allows — e.g. a GET request reaching an action that called `$this->request->allowMethod('post')`.
 */
final class MethodNotAllowedException extends HttpException
{
    public function __construct(string $message = 'Method not allowed', int $statusCode = 405)
    {
        parent::__construct($message, $statusCode);
    }
}
