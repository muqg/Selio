<?php
namespace selio;

use Exception;

class SelioException extends Exception {
    public function __construct(string $message, int $responseCode = 500) {
        http_response_code($responseCode);
        Core::logFlagged($message, 'Exception');
        parent::__construct($message);
    }
}

final class ClassException extends SelioException {}

final class DatabaseException extends SelioException {}

final class LocalizationException extends SelioException {}