<?php
declare(strict_types=1);

namespace TestApp\Controller;

use Elone\Core\Controller;

/**
 * Exposes one action per supported/unsupported parameter type, used only to build `ReflectionMethod` fixtures for
 * `Dispatcher::resolveArguments()` tests — never actually dispatched.
 */
class ConversionController extends Controller
{
    public function withInt(int $value): void
    {
    }

    public function withFloat(float $value): void
    {
    }

    public function withBool(bool $value): void
    {
    }

    public function withString(string $value): void
    {
    }

    /**
     * @param array<mixed> $value
     */
    public function withArray(array $value): void
    {
    }
}
