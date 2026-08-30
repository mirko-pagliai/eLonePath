<?php
declare(strict_types=1);

namespace Elone\Core\Exception;

/**
 * Exception thrown when an action declares a parameter of a type this framework does not know how to build from a
 * URL segment (e.g. `array`, `object`, `callable`).
 *
 * Unlike the other `HttpException` subclasses, this is not a client-facing routing failure — it signals a mismatch
 * between the action's signature and what `Dispatcher::convert()` supports, so it defaults to a 500 status code.
 */
final class UnsupportedParameterTypeException extends HttpException
{
    public function __construct(string $message = 'Unsupported parameter type', int $statusCode = 500)
    {
        parent::__construct($message, $statusCode);
    }
}
