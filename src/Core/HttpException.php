<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(private readonly int $statusCode, string $message = 'Bad Request')
    {
        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
