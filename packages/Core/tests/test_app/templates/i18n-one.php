<?php
declare(strict_types=1);

/**
 * Fixture for `ExtractMessagesCommandTest` — three domains in one file: `default` (bare `__()`), `validation`
 * and `errors` (both via `__d()`), proving each domain found gets its own correctly-named `.pot`.
 */

echo __('Welcome back');
echo __d('validation', 'The {0} attribute is required.');
echo __d('errors', 'Something went wrong.');
