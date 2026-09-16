<?php
declare(strict_types=1);

namespace App\Exception;

use Elone\Core\Exception\HttpException;

/**
 * Exception thrown when a requested documentation page cannot be found — either its basename doesn't resolve
 * to a real file or contains characters that could never resolve to one in the first place.
 */
final class DocumentNotFoundException extends HttpException
{
    public function __construct(string $message = 'Documentation page not found', int $statusCode = 404)
    {
        parent::__construct($message, $statusCode);
    }
}
