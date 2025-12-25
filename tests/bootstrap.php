<?php
declare(strict_types=1);

// Define directory separator for cross-platform compatibility
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// Define the root directory of the project
if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

// Additional constants for tests, if needed
if (!defined('RESOURCES')) {
    define('RESOURCES', ROOT . DS . 'tests' . DS . 'resources');
}

if (!defined('TMP')) {
    define('TMP', sys_get_temp_dir() . DS . 'elonepath-engine');
}

if (!file_exists(TMP)) {
    mkdir(TMP, 0777, true);
}

/**
 * Includes the main bootstrap.
 *
 * @link config/bootstrap.php
 */
require_once ROOT . '/config/bootstrap.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
