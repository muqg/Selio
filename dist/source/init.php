<?php
use selio\Core;
use selio\ClassException;

const SELIO_VERSION = 'rc_18.1';

define('SELIO_ROOT', strtr(realpath(__DIR__ . '/..'), '\\', '/'));

spl_autoload_register('selio_autoload');

require Core::getIncludePath('settings.php');
require Core::getIncludePath('selio/Exceptions.php');



// ===========================

function selio_autoload($classname) {
    $path = strtr($classname, '\\', '/');
    $file = SELIO_ROOT . "/source/classes/$path.php";
    if((@include $file) === false)
        throw new ClassException("Class '$path' not found.");
    return true;
}

/**
 * Returns a setting value from the settings.php file. Alternatively the global
 * constant SELIO_SETTINGS may be used but this function will aim to simplify
 * usage and perform safety and crash checks in the future.
 * @param string $key The key to the setting.
 */
function setting(string $key) {
    return SELIO_SETTINGS[$key] ?? null;
}