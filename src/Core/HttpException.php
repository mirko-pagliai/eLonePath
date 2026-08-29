<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class HttpException extends RuntimeException
{
    public function __construct(private readonly int $statusCode, string $message = '')
    {
        if ($message === '') {
            $message = match ($statusCode) {
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                500 => 'Internal Server Error',
                default => 'HTTP Error',
            };
        }

        parent::__construct($message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
