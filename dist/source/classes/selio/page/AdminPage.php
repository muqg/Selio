<?php
namespace selio\page;

/**
 * Class should be extended by any administration page class.
 */
abstract class AdminPage extends ContentPage {
    // Authentication should be required for administration pages.
    protected $auth = true;

    function __construct(string $pageName) {
        parent::__construct($pageName, 'admin');

        // Authentication should be mandatory for administration pages.
        $this->auth = true;
        $this->setTitle('Administration');
    }
}