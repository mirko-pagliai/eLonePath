<?php
declare(strict_types=1);

namespace App\Core\Exception;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(string $message = 'Bad Request', private readonly int $statusCode = 400)
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
