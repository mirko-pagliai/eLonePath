<?php
declare(strict_types=1);

// Define directory separator for cross-platform compatibility
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

const DEBUG = true;
define('ROOT', dirname(__DIR__));
const RESOURCES = ROOT . DS . 'tests' . DS . 'resources';
define('TMP', sys_get_temp_dir() . DS . 'elonepath-engine');

if (!file_exists(TMP)) {
    mkdir(TMP, 0777, true);
}

/**
 * Includes the main bootstrap.
 *
 * All constants that the main bootstrap should not override have already been set at this point.
 *
 * @link config/bootstrap.php
 */
require_once ROOT . '/config/bootstrap.php';

// Set error reporting for tests
error_reporting(E_ALL);
ini_set('display_errors', '1');
