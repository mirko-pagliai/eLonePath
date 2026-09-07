<?php
declare(strict_types=1);

use Elone\Core\Translator;

if (!function_exists('__')) {
    /**
     * Translates a string using the default translation domain.
     *
     * @param string $string The string to translate.
     * @param mixed ...$args Values to substitute into the translation message.
     * @return string The translated string.
     */
    function __(string $string, mixed ...$args): string
    {
        return Translator::trans($string, 'default', ...$args);
    }
}

if (!function_exists('__d')) {
    /**
     * Translates a string using the specified translation domain.
     *
     * @param string $domain The translation domain to use.
     * @param string $string The string to translate.
     * @param mixed ...$args Values to substitute into the translation message.
     * @return string The translated string.
     */
    function __d(string $domain, string $string, mixed ...$args): string
    {
        return Translator::trans($string, $domain, ...$args);
    }
}

if (!function_exists('debug')) {
    /**
     * Provides a global alias for the `dump()` function.
     *
     * Outputs debug information for the provided variables.
     *
     * @param mixed ...$vars A variadic list of variables to dump for debugging purposes.
     * @return void
     * @see https://symfony.com/doc/current/components/var_dumper.html#the-dump-function
     */
    function debug(mixed ...$vars): void
    {
        dump(...$vars);
    }
}

if (!function_exists('env')) {
    /**
     * Retrieves the value of an environment variable.
     *
     * Returns the value of the specified environment variable, or a default value if the variable is not set.
     *
     * @param string $key The name of the environment variable.
     * @param mixed $default The value to return if the environment variable is not set. Defaults to null.
     * @return mixed The value of the environment variable, or the default value if not set.
     */
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}

if (!function_exists('h')) {
    /**
     * Provides a global alias for the `htmlspecialchars()` function.
     *
     * Converts special characters to HTML entities.
     *
     * @param string $string The string to be converted.
     * @param int $flags A bitmask of one or more flags, defaulting to ENT_QUOTES | ENT_SUBSTITUTE.
     * @param string|null $encoding The character encoding to use. If null, the default encoding is used.
     * @param bool $double_encode Whether to convert already encoded entities. Default is true.
     * @return string The converted string with special characters replaced by HTML entities.
     */
    function h(
        string $string,
        int $flags = ENT_QUOTES | ENT_SUBSTITUTE,
        ?string $encoding = null,
        bool $double_encode = true,
    ): string {
        return htmlspecialchars($string, $flags, $encoding, $double_encode);
    }
}
