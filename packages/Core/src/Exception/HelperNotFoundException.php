<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

use RuntimeException;

/**
 * Exception thrown when a template tries to use a helper that was never registered via `View::loadHelper()`.
 */
final class HelperNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Helper not found')
    {
        parent::__construct($message);
    }
}
