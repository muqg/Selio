<?php
namespace selio;

use DateInterval;
use selio\db\Database;
use selio\db\DatabaseConnection;
use selio\page\Component;


// ===========================
require Core::getIncludePath('BaseExtension.php');
// ===========================


/**
 * This class provides basic functionality
 * and serves as a wrapper around static Selio classes
 * in order to avoid unnecessary use statements
 * and simplify extensibility.
 */
abstract class Base {
    /**
     * This trait allows for the addition of custom extension methods and
     * fields to any class derived from the Base class.
     */
    use \selio\BaseExtension;

    /**
     * Override with the name of the database connection to be automatically
     * instantiated. It then holds the object instance of that connection. Refer
     * to db.php for more information.
     * @see Base::connectToDatabase() for more information.
     */
    protected $connection = null;

    /**
     * Stores the component objects created by the getComponent method.
     * @var array
     */
    private static $components = [];

    /**
     * @param boolean $autoConnect Indicates whether to instantiate a database
     * connection automatically, based on the class' connection property.
     */
    public function __construct(bool $autoConnect = false) {
        $connectionName = $this->connection;
        if($autoConnect && $connectionName)
            $this->connection = $this->connectToDatabase($connectionName);
    }

    /**
     * Invokes a component object.
     * @param string $component The component name with fully qualified namespace
     * relative to the \components namespace. Namespace may be separated
     * with either / or \.
     * @param mixed ...$args Variable number of arguments that will be provided
     * to the constructor of the component object.
     */
    public final static function invokeComponent(string $component, ...$args) : Component {
        $component = 'components\\' . strtr($component, '/', '\\');
        return new $component(...$args);
    }

    /**
     * Returns an existing component object instance. If there is no existing
     * instance, creates and stores one for further usage.
     * @param string $component The component name with fully qualified namespace
     * relative to the \components namespace. Namespace may be separated
     * with either / or \.
     * @param mixed ...$args Variable number of arguments that will be provided
     * to the constructor of the component object. Note that these arguments will
     * be passed only the first time the method is called for a component.
     */
    public final static function getComponent(string $component, ...$args) : Component {
        $component = 'components\\' . strtr($component, '/', '\\');
        if(!array_key_exists($component, self::$components))
            self::$components[$component] = new $component(...$args);
        return self::$components[$component];
    }

    /**
     * Calls the build method of an existing component object, passing an
     * optional variable number of arguments to it. If there is no existing
     * instance, creates and stores one for further usage.
     * @param string $component The component name with fully qualified namespace
     * relative to the \components namespace. Namespace may be separated
     * with either / or \.
     * @param mixed ...$args Variable number of arguments to be passed to the build
     * method.
     */
    public final static function buildComponent(string $component, ...$args) : Component {
        $instance = self::getComponent($component);
        $instance->build(...$args);
        return $instance;
    }

    /**
     * Returns the database connection object.
     * @return \selio\db\DatabaseConnection
     */
    // Note: should not have a return type since it throws error on null connection.
    public final function getConnection() {
        return $this->connection;
    }





    /**
     * Logs a message and the backtrace to where it occured.
     * @param string $message The message to be saved in the log file.
     * @param integer $backtrace A positive backtrace index to where the
     * message occured. A negative index does not log backtrace at all.
     */
    public function log(string $message, int $backtrace = 1) {
        Core::log($message, $backtrace);
    }

    /**
     * Returns a Selio URI. This consists of a path, relative to website root,
     * with prepended install path and language, based on the current environment
     * constants.
     * @param string $path The target URI path.
     */
    public final function getURI(string $path = '') : string {
        return Core::getURI($path);
    }

    /**
     * Gets the path to the include folder appending any additional path
     * string provided as a parameter.
     * @param string $path An optional path string to be appended.
     */
    public final function getIncludePath(string $path = '') : string {
        return Core::getIncludePath($path);
    }

    /**
     * Gets an assets URI while appending the provided path parameter.
     * @param string $path An optional path string to be appended to the assets URI.
     */
    public final function getAssetsURI(string $path = '') : string {
        return Core::getAssetsURI($path);
    }

    /**
     * Gets the local assets path (server path) while appending the provided
     * path parameter.
     * @param string $path An optional path string to be appended to the assets path.
     */
    public final function getAssetsPath(string $path = '') : string {
        return Core::getAssetsPath($path);
    }





    /**
     * Initializes a database connection for the named data (as in db.php).
     * @see Database::connect() For the wrapped method.
     * @param string $name The name to the connection data.
     */
    public function connectToDatabase(string $name) : DatabaseConnection {
        return Database::connect($name);
    }





    /**
     * Loads a locale file.
     * @param string $name The name of the locale file.
     */
    public function loadLocale(string $name) {
        Localization::load($name);
    }

    /**
     * Returns a localized element. Accepts a variable number of arguments which
     * are used to format a string element.
     * @param string $key The key for the localized element. A nested key may be
     * provided where each array level is separated with a slash (/).
     * @param mixed ...$args Variable number of arguments to replace the format
     * placeholders with in the provided order. Can pass a single array of
     * key/value pairs to replace named format placeholders instead.
     */
    public function localize(string $key, ...$args) {
        return Localization::get($key, ...$args);
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
    public final function autoCache(string $key, DateInterval $duration, callable $item, ...$params) {
        return Cache::autoCache($key, $duration, $item, ...$params);
    }

    /**
     * Retrieves cache's content or NULL if it has expired and needs re-caching.
     * @param string $key The cache file's key.
     */
    public final function getCache(string $key) {
        return Cache::getCache($key);
    }

    /**
     * Caches a string or array value.
     * @param string $key The cache file's key.
     * @param DateInterval $duration The duration the cache is valid for.
     * @param mixed $item The string or array item to be cached.
     * @return mixed The cached string or array.
     */
    public final function setCache(string $key, DateInterval $duration, $item) {
        return Cache::setCache($key, $duration, $item);
    }




    /**
     * Runs an event.
     * @param string $name The name of the event to run.
     * @param mixed $eventData The event data. An instance of Event can be
     * passed instead thus allowing to use objects extending Selio's Event
     * class when running the event.
     * @param array $eventOptions Options to be set on the Event object.
     */
    public final function runEvent(string $name, $eventData = null, array $eventOptions = []) : Event {
        return Event::run($name, $eventData, $eventOptions);
    }

    /**
     * Subscribes a valid callable to an event.
     * @param string $eventName The name of the event to subscribe to.
     * @param string $subscription The subscribing callable.
     */
    public final function subscribeToEvent(string $eventName, callable $subscription) : callable {
        return Event::subscribe($eventName, $subscription);
    }

}