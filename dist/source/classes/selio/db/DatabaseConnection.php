<?php
namespace selio\db;

use PDO;
use selio\DatabaseException;


/**
 * Class represents an extended version of PDO. It extends query and execution
 * methods in order to make them throw DatabaseException instead of returning
 * false on failure.
 */
class DatabaseConnection extends PDO {
    /**
     * Execute an SQL statement and return the number of affected rows.
     * Method is extended to throw a DatabaseException on failure instead
     * of returning false. See the original PDO::exec() for more information.
     * @param string $query The SQL statement to prepare and execute. Data
     * inside the query should be properly escaped.
     */
    public function exec($query) : int {
        $rowsChanged = parent::exec($query);
        if($rowsChanged === false)
            throw new DatabaseException("Failed to execute query: $query");
        return $rowsChanged;
    }

    /**
     * Executes an SQL statement, returning a result set as a DatabaseStatement
     * object. Method is extended to throw a DatabaseException on failure instead
     * of returning false. See the original PDO::query() for more information.
     * @param string $statement The SQL statement to prepare and execute. Data
     * inside the query should be properly escaped.
     */
    public function query($statement) : DatabaseStatement {
        $result = parent::query($statement);
        if($result === false)
            throw new DatabaseException("Failed querying database for statement: $statement");
        return $result;
    }
}