<?php
namespace selio\page;

/**
 * Serves as a base class to all other Selio components.
 */
abstract class Component extends \selio\Base {
    /**
     * Holds the current page singleton's database connection object instance.
     * @var \selio\db\DatabaseConnection
     */
    protected $connection;

    /**
     * Holds the reference to the current page instance singleton.
     * @var Page
     */
    protected $pageInstance;

    public function __construct() {
        // Component should not attempt to automatically instantiate a new
        // database connection and use the current page's one instead.
        parent::__construct(false);

        $this->pageInstance = Page::getPageInstance();
        $this->connection = $this->pageInstance->getConnection();
    }

    /**
     * Method should provide the main content and functionality of the component.
     * It is called by Selio when components are used as page routes and is
     * provided with the URL arguments in this case.
     */
    public abstract function build();
}