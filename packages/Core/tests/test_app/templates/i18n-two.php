<?php
declare(strict_types=1);

/**
 * Fixture for `ExtractMessagesCommandTest` — `'Welcome back'` repeats `i18n-one.php`'s own message, to prove
 * deduplication across files; the non-literal call must be skipped, not crash the whole scan.
 */

echo __('Welcome back');

$dynamicMessage = 'not a literal, must be skipped';
echo __($dynamicMessage);
