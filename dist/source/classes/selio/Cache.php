<?php
namespace selio;

use DateInterval;
use DateTime;
use InvalidArgumentException;

final class Cache {
    /**
     * Initialization control property.
     * @var boolean
     */
    private static $isInitialized = false;

    /**
     * Path to the cache folder.
     * @var string
     */
    private static $cachePath = '';

    /**
     * Performs initialization for the class. Method is called when the file is
     * first included.
     */
    public static function init() {
        if($isInitialized)
            return;

        self::$cachePath = Core::getIncludePath('selio/cache');
        mkdir(self::$cachePath, 0777, true);

        self::$isInitialized = true;
    }

    /**
     * Caches a callable item if it is not cached or the previous cache has
     * expired and then returns the cached string or array content.
     * @param string $key The cache file's key.
     * @param DateInterval $duration The duration the cache is valid for.
     * @param callable $item The callable to be cached.
     * @param mixed ...$params Additional parameters to be passed to the callable item.
     * @return mixed Returns the cached string or array.
     */
    public static function autoCache(string $key, DateInterval $duration, callable $item, ...$params) {
        $cacheContent = self::getCache($key);
        if($cacheContent === null) {
            ob_start();
            $res = call_user_func($item, ...$params);
            $itemValue = ob_get_clean() ? : $res;

            $cacheContent = self::setCache($key, $duration, $itemValue);
        }
        return $cacheContent;
    }

    /**
     * Retrieves cache's content or NULL if it has expired and needs re-caching.
     * @param string $key The cache key.
     */
    public static function getCache(string $key) {
        $cacheFile = self::getCacheFilePath($key);
        $cache = @include $cacheFile;

        if(!$cache)
            return null;

        $now = new DateTime();
        $expiration = new DateTime($cache['expiration']);
        $hasExpired = $now > $expiration;

        return !$hasExpired ? $cache['content'] : null;
    }

    /**
     * Caches a string or array value.
     * @param string $key The cache file's key.
     * @param DateInterval $duration The duration the cache is valid for.
     * @param mixed $item The string or array item to be cached.
     * @return mixed The cached string or array.
     */
    public static function setCache(string $key, DateInterval $duration, $item) {
        if(!(is_array($item) || is_string($item))) {
            $type = gettype($item);
            throw new InvalidArgumentException(
                "Cache item can only be of type string or array, $type given."
            );
        }

        $expiration = (new DateTime())->add($duration)->format('Y-m-d H:i:s');

        $cache = [
            'content' => $item,
            'expiration' => $expiration
        ];
        $cache = '<?php return ' . var_export($cache, true) . ';';

        $cacheFile = self::getCacheFilePath($key);
        file_put_contents($cacheFile, $cache);

        return $item;
    }

    /**
     * Gets the path to a cache file.
     * @param string $name The cache file's name.
     */
    private static function getCacheFilePath(string $name) : string {
        return self::$cachePath . "/$name";
    }
}


// ===========================


Cache::init();
