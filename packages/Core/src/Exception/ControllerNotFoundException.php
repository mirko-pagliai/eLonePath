<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when a requested controller cannot be found.
 */
final class ControllerNotFoundException extends HttpException
{
    public function __construct(string $message = 'Controller not found', int $statusCode = 404)
    {
        parent::__construct($message, $statusCode);
    }
}
