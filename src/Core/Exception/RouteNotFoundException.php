<?php
declare(strict_types=1);

namespace App\Core\Exception;

final class RouteNotFoundException extends HttpException
{
    public function __construct(string $message = 'Route not found')
    {
        parent::__construct($message);
    }
}
