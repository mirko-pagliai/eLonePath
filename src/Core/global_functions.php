<?php
declare(strict_types=1);

if (!function_exists('debug')) {
    /**
     * Provides a global alias for the dump function.
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
