<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
ini_set('intl.default_locale', 'en_US');

define('ROOT', dirname(__DIR__));
const TEST_APP = ROOT . '/tests/test_app/';
