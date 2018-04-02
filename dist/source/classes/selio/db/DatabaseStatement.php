<?php
namespace selio\db;

use PDOStatement;
use selio\DatabaseException;

/**
 * Class respresents an extended version of PDOStatement. It extends execute
 * method in order to make it throw DatabaseException instead of returning
 * false on failure.
 */
class DatabaseStatement extends PDOStatement {
    /**
     * Executes a prepared statement. Extends the method by making it throw a
     * DatabaseException instead of returning false on failure. See the original
     * PDOStatement::execute() method for more information.
     * @param array An array of values with as many elements as there are bound
     * parameters in the SQL statement being executed.
     */
    public function execute($input_parameters = null) : bool {
        $result = parent::execute($input_parameters);
        if($result === false)
            throw new DatabaseException("Failed executing statement: {$this->queryString}");
        return $result;
    }
}