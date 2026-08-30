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
     * @param string $rootPath The root path for the application.
     * @param string $controllerNamespace The namespace for the controllers.
     * @param bool $debug Indicates whether the application is in debug mode.
     *
     * @return void
     */
    public function __construct(
        private string $rootPath,
        private string $controllerNamespace,
        private bool $debug = false,
    ) {
    }

    /**
     * Retrieves the root path of the application.
     *
     * @return string The root path of the application.
     */
    public function rootPath(): string
    {
        return rtrim($this->rootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieves the path to the templates' directory.
     *
     * @return string The full path to the templates directory.
     */
    public function templatesPath(): string
    {
        return $this->rootPath() . 'templates' . DIRECTORY_SEPARATOR;
    }

    /**
     * Retrieves the namespace for the controllers.
     *
     * @return string The controller namespace.
     */
    public function controllerNamespace(): string
    {
        return $this->controllerNamespace;
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
