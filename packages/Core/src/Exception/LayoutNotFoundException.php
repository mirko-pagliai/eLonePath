<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when a specific layout is not found.
 */
final class LayoutNotFoundException extends HttpException
{
    public function __construct(string $message = 'Layout not found')
    {
        parent::__construct($message);
    }
}
