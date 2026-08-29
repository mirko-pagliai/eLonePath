<?php
declare(strict_types=1);

namespace App\Core\Exception;

/**
 * Exception thrown when a specified action cannot be found.
 *
 * This exception should be used to indicate that a requested action does not exist or cannot be resolved during
 * application execution.
 */
final class ActionNotFoundException extends HttpException
{
    public function __construct(string $message = 'Action not found')
    {
        parent::__construct($message);
    }
}
