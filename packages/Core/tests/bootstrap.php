<?php
declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
ini_set('intl.default_locale', 'en_US');

define('ROOT', dirname(__DIR__));

const APP_NAMESPACE = 'TestApp';

const TEMPLATES = ROOT . '/tests/test_app/templates/';

require ROOT . '/config/bootstrap.php';
