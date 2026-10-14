<?php

declare(strict_types=1);

if (!defined('APP_NAME')) {
    define('APP_NAME', 'SmartDine');
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', '/');
}

$databaseHost = getenv('SMARTDINE_DB_HOST') ?: '127.0.0.1';
$databaseName = getenv('SMARTDINE_DB_NAME') ?: 'smartdine';
$databaseUser = getenv('SMARTDINE_DB_USER') ?: 'root';
$databasePassword = getenv('SMARTDINE_DB_PASS') ?: '';
$databaseCharset = 'utf8mb4';

if (!defined('DB_HOST')) {
    define('DB_HOST', $databaseHost);
}
if (!defined('DB_NAME')) {
    define('DB_NAME', $databaseName);
}
if (!defined('DB_USER')) {
    define('DB_USER', $databaseUser);
}
if (!defined('DB_PASS')) {
    define('DB_PASS', $databasePassword);
}
if (!defined('DB_CHARSET')) {
    define('DB_CHARSET', $databaseCharset);
}
