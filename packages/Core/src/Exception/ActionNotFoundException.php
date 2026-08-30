<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when a specified action cannot be found.
 *
 * This exception should be used to indicate that a requested action does not exist or cannot be resolved during
 * application execution.
 */
final class ActionNotFoundException extends HttpException
{
    public function __construct(string $message = 'Action not found', int $statusCode = 404)
    {
        parent::__construct($message, $statusCode);
    }
}
