<?php
/**
 * This file is part of CopyCon system
 *
 * Web initializer
 *
 * @author Thiago Paes <mrprompt@gmail.com>
 */
if (!array_key_exists('REQUEST_URI', $_SERVER)) {
    return false;
}

$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Run Forest, run!
$app = require 'bootstrap.php';
$app->run();