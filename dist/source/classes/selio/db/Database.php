<?php
namespace selio\db;

use PDO;
use selio\Core;
use selio\DatabaseException;


/**
 * This class handles database connections via PDO. It also loads the database
 * dataset file and attempts to keep track of active database connections.
 */
final class Database {
    /**
     * The class that is used in place of PDOStatement.
     */
    const SATEMENT_CLASS = '\\selio\\db\\DatabaseStatement';

    /**
     * Initialization control property.
     * @var boolean
     */
    private static $isInitialized = false;

    /**
     * Holds the db.php file's data.
     * @var array
     */
    private static $dataset = null;

    /**
     * Holds the active connection objects.
     * Notice: Datanase::closeConnection() should be used in order to close a
     * connection initialized via this class.
     * @var array
     */
    private static $activeConnections = [];

    /**
     * Performs initialization for the class. Method is called when the file is
     * first included.
     */
    public static function init() {
        if($isInitialized)
            return;

        self::$dataset = require Core::getIncludePath('db.php');

        self::$isInitialized = true;
    }

    /**
     * Creates a new connection for the specified connection name (as in db.php).
     * If a connection of that name is already active returns the active instance instead.
     * Notice: Connections initialized via this class should be closed using
     * Database::closeConnection() method.
     * @param string $name The name of the connection (as in db.php).
     * @param string $connectionData The connection data in valid format. If not
     * provided it is automatically loaded from the db.php file.
     */
    public static function connect(string $name, array $connectionData = []) : DatabaseConnection {
        $activeConnection = self::$activeConnections[$name] ?? null;
        if($activeConnection) {
            return $activeConnection;
        }
        else {
            if(!$connectionData)
                $connectionData = self::getConnectionData($name);

            $dsn = "{$connectionData['driver']}:host={$connectionData['host']};dbname={$connectionData['dbname']}";
            $con = new DatabaseConnection(
                $dsn, $connectionData['username'], $connectionData['password']
            );

            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $con->setAttribute(PDO::ATTR_STATEMENT_CLASS, [self::SATEMENT_CLASS]);

            self::$activeConnections[$name] = $con;
            return $con;
        }
    }

    /**
     * Closes a connection on the active connections list.
     * This method is meant to be called in order to clear
     * the tracked instance in this class when a connection
     * needs to be closed before script execution terminates.
     * @param string $name The key name of the connection to be closed (as in db.php).
     */
    public static function closeConnection(string $name) {
        self::$activeConnections[$name] = null;
        return null;
    }

    /**
     * Gets a connection data array from the db.php file. Throws an Exception
     * if the name is invalid.
     * @param string $name The name of the connection data.
     */
    public static function getConnectionData(string $name) : array {
        if(array_key_exists($name, self::$dataset))
            return self::$dataset[$name];
        throw new DatabaseException("Invalid connection data name '$name'.");
    }
}


// ===========================


Database::init();
