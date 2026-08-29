<?php
declare(strict_types=1);

namespace App\Core\Exception;

/**
 * Exception thrown when a requested controller cannot be found.
 *
 * This exception is typically used to indicate that the application failed to locate a controller required to handle a
 * specific request.
 */
final class ControllerNotFoundException extends HttpException
{
    public function __construct(string $message = 'Controller not found')
    {
        parent::__construct($message);
    }
}
