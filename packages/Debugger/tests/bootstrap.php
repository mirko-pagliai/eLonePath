<?php
declare(strict_types=1);

/**
 * `Debugger` isn't self-contained — its classes reference `App\Story\Game` and friends
 * directly, so its tests need the main app's autoloader, not one built from this package alone. This bootstrap
 * assumes it's always run from within the full project (`packages/Debugger` installed as a path dependency of the
 * root `composer.json`), never as a fully standalone package.
 */
require dirname(__DIR__, 3) . '/vendor/autoload.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
ini_set('intl.default_locale', 'en_US');

define('ROOT', dirname(__DIR__));
