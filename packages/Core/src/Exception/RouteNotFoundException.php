<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when a requested route cannot be found.
 *
 * This exception indicates that the specified route does not exist or could not be resolved within the application's
 * routing system.
 */
final class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Route not found')
    {
        parent::__construct($message);
    }
}
