<?php
declare(strict_types=1);

// Define directory separator for cross-platform compatibility
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Include the main bootstrap
require_once dirname(__DIR__) . DS . 'config' . DS . 'bootstrap.php';

// Additional constants for tests, if needed
if (!defined('TESTS_RESOURCES')) {
    define('TESTS_RESOURCES', ROOT . DS . 'tests' . DS . 'resources');
}

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
