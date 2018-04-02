<?php
const SELIO_TARGET_PATH = __DIR__ . '/..';
const DOT_PATHS = ['.', '..'];
CONST SELIO_PATHS = [
    'assets/css/selio',
    'assets/js/selio',
    'source/classes/ajax/Selio.php',
    'source/classes/components/selio',
    'source/classes/pages/admin/Selio.php',
    'source/classes/pages/selio',
    'source/classes/routes/selio',
    'source/classes/selio',
    'source/include/selio',
    'source/init.php'
];
const SELIO_IGNORE = [
    'source/include/selio/setup',
    '.htaccess',
    'setup.php',
    'update.php'
];

function removeRecursively(string $path) {
    if(is_dir($path)) {
        $items = array_diff(scandir($path), DOT_PATHS);
        foreach ($items as $item)
            removeRecursively("$path/$item");
        rmdir($path);
    }
    else if(is_file($path))
        unlink($path);
}

function moveRecursively(string $currentPath) {
    $ignorePath = str_replace(__DIR__ . '/', '', $currentPath);
    if(in_array($ignorePath, SELIO_IGNORE))
        return;

    $targetPath = str_replace(__DIR__, SELIO_TARGET_PATH, $currentPath);
    if(is_dir($currentPath)) {
        if(!is_dir($targetPath))
            mkdir($targetPath);
        $items = array_diff(scandir($currentPath), DOT_PATHS);
        foreach ($items as $item)
            moveRecursively("$currentPath/$item");
    }
    else
        rename($currentPath, $targetPath);
}

function update() {
    // Attempts to confirm that update.php is
    // placed within the dist folder of Selio.
    if(file_exists('setup.php') && file_exists('source/init.php')) {
        foreach(SELIO_PATHS as $path)
            removeRecursively(SELIO_TARGET_PATH . "/$path");
        moveRecursively(__DIR__);
        removeRecursively(__DIR__);
        echo '<h1>Updated successful!</h1>';
    }
    else
        echo 'update.php must be placed in the dist folder of a Selio distribution.';
}

// ===========================

@update();
