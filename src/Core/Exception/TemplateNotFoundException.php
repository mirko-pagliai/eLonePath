<?php
declare(strict_types=1);

namespace App\Core\Exception;

use RuntimeException;

/**
 * Exception thrown when a requested template is not found.
 */
class TemplateNotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Template not found')
    {
        parent::__construct($message);
    }
}
