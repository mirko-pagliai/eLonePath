<?php
declare(strict_types=1);

namespace Elone\Core;

/**
 * Represents the configuration settings for an application.
 *
 * This immutable class encapsulates paths and debugging flags that define the application's operational behavior.
 */
final readonly class Configuration
{
    /**
     * Constructor method.
     *
     * @param bool $debug Whether the application is in debug mode. Defaults to `false`.
     *
     * @return void
     */
    public function __construct(private bool $debug = false)
    {
    }

    /**
     * Retrieves the debug mode status.
     *
     * @return bool `True` if debug mode is enabled, `false` otherwise.
     */
    public function debug(): bool
    {
        return $this->debug;
    }
}
