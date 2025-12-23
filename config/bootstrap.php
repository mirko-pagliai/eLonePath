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

// Define config directory
if (!defined('CONFIG')) {
    define('CONFIG', ROOT . DS . 'config');
}

// Define resources directory
if (!defined('RESOURCES')) {
    define('RESOURCES', ROOT . DS . 'resources');
}

// Include Composer autoloader if exists
$composerAutoload = ROOT . DS . 'vendor' . DS . 'autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}
