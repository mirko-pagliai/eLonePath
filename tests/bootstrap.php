<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require ROOT . '/vendor/autoload.php';

date_default_timezone_set('UTC');
mb_internal_encoding('UTF-8');
ini_set('intl.default_locale', 'en_US');

const TEST_APP = ROOT . '/tests/test_app';

const STORIES = TEST_APP . '/stories';

const WEBROOT = ROOT . '/tests/test_app/webroot';
