<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when a specified action cannot be found.
 */
final class ActionNotFoundException extends HttpException
{
    public function __construct(string $message = 'Action not found', int $statusCode = 404)
    {
        parent::__construct($message, $statusCode);
    }
}
