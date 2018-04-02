<?php
namespace selio;

/**
 * Provides general utility methods that are not related directly to Selio's
 * general structure and behaviour.
 */
final class Utility {

    // ===========================
    // String manipulation.
    // ===========================

    /**
     * Generates a random string.
     * @param integer $length The length of the generated string.
     * @param string $keyspace The symbols to randomly choose from.
     */
    public static function randomString(int $length, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyz') : string {
        $string = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i)
            $string .= $keyspace[mt_rand(0, $max)];
        return $string;
    }



    // ===========================
    // Array manipulation.
    // ===========================

    /**
     * Gets an array element by recursively digging into nested arrays based on
     * the provided key string.
     * @param string $key Represents the sequence of keys to the nested array
     * element as a string of keys separated by a slash (/).
     * @param array $arr The array.
     */
    public static function getArrayElement(string $key, array $arr) {
        $value = $arr;
        $exploded = explode('/', $key);
        foreach($exploded as $k) {
            if(is_array($value))
                $value = $value[$k] ?? null;
        }
        return $value;
    }

    /**
     * Shuffles an array using the modern Fisher-Yattes algorithm. It shuffles
     * the array's values and does not maintain key association.
     * @param array $items The array to shuffle.
     */
    public static function fyShuffle(array $items) : array {
        $items = array_values($items);
        for($i = count($items) - 1; $i > 0; $i--) {
            $rand = mt_rand(0, $i);
            $temp = $items[$i];
            $items[$i] = $items[$rand];
            $items[$rand] = $temp;
        }
        return $items;
    }



    // ===========================
    // Files and folders.
    // ===========================

    /**
     * Checks whether a directory is empty.
     * @param string $dir The target directory.
     */
    public static function isEmptyDirectory(string $dir) {
        if(!is_readable($dir))
            return null;
        $handle = opendir($dir);
        while(($entry = readdir($handle)) !== false) {
            if($entry != '.' && $entry != '..')
                return false;
        }
        return true;
    }

    /**
     * Checks whether an input path is a directory traversal or not.
     * @param string $basePath The base path that should not be traversed.
     * @param string $inputPath The input path that will be tested.
     */
    public static function isDirectoryTraversal(string $basePath, string $inputPath) : bool {
        $basePath = realpath($basePath);
        $target = realpath("$basePath/$inputPath");
        return ($target && strpos($target, $basePath) === 0) ? false : true;
    }

    /**
     * Returns the disk memory usage of a folder and all of its contents in bytes.
     * @param string $path The path to the folder.
     */
    public static function getFolderSize(string $path) : int {
        $path = realpath($path);
        $bytes = 0;

        if(is_dir($path)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path)
            );

            $bytes = 0;
            foreach($iterator as $item);
                $bytes += $item->getSize();
        }

        return $bytes;
    }
}