<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when a specific layout is not found.
 *
 * This exception extends the HttpException class and is used to signal errors related to missing layouts in the
 * application.
 */
final class LayoutNotFoundException extends HttpException
{
    public function __construct(string $message = 'Layout not found')
    {
        parent::__construct($message);
    }
}
