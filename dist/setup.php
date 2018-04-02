<?php
use selio\Core;

const SELIO_INCLUDE_FILES = [
    '.htpasswd',
    'BaseExtension.php',
    'db.php',
    'settings.php'
];

moveSetupFiles();
require 'source/init.php';
cleanupSetupPath();

header('Location: /');


// ===========================

function moveSetupFiles() {
    $setupDir = realpath('source/include/selio/setup');
    $targetDir = realpath('source/include');

    foreach(SELIO_INCLUDE_FILES as $file) {
        $oldPath = realpath($setupDir) . "/$file";
        $newPath = realpath($targetDir) . "/$file";
        rename($oldPath, $newPath);
    }

    $pagesPath = 'source/classes/pages';
    foreach(['pub', 'admin'] as $pageType) {
        $dir = "$pagesPath/$pageType";
        if(!file_exists($dir))
            mkdir($dir, 0777, true);
    }
    rename("$setupDir/Front.php", "$pagesPath/pub/Front.php");
    rename("$setupDir/admin_Front.php", "$pagesPath/admin/Front.php");
}

function getSetupPath(string $path = '') {
    return Core::getIncludePath('selio/setup') . $path ? "/$path" : '';
}

function cleanupSetupPath() {
    array_map('unlink', glob(getSetupPath() . '/*.*'));
    unlink('setup.php');
}

/*function replaceFileLine(string $file, string $search, string $replace) {
    $file = realpath($file);
    $tmpFile = $file . '.tmp';
    rename($file, $tmpFile);

    $isReplaced = false;
    $lines = file($tmpFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINE);
    foreach($lines as $line) {
        if(!$isReplaced && strpos($line, $search) !== false) {
            file_put_contents($file, $replace, FILE_APPEND);
            $isReplaced = true;
        }
        file_put_contents($file, $line, FILE_APPEND);
    }
}*/