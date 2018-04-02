<?php
namespace selio\page;
use ArrayAccess;
use selio\SelioException;

/**
 * This class allows for an easy creation of
 * ajax page component classes that hold a reference
 * to the current ajax page instance and allow for the
 * usage of its features. It is also used to handle the
 * request when routing.
 */
abstract class AjaxComponent extends Component implements ArrayAccess {
    /**
     * Holds the keys that should be present in the reuqest data for each method.
     * Override this property in order to provide validation functionality.
     * @see AjaxPage::isDataValid() for more information.
     * @var array
     */
    protected $validation = [];

    /**
     * Method is called by Selio and it executes the correct method for the
     * request while confirming that the 'main' page instance is of AjaxPage.
     */
    public final function build(...$urlArgs) {
        if(!$this->pageInstance instanceof AjaxPage) {
            throw new SelioException(
                'AjaxComponent can only be built on AJAX request '
                . 'where the main page isntance is of an AjaxPage.'
            );
        }

        $this->onRequestStart(...$urlArgs);
        $classMethod = 'on' . $this->pageInstance->getMethod() . 'Request';
        $this->$classMethod(...$urlArgs);
        $this->onRequestEnd(...$urlArgs);
    }

    /*
     * Implementation for the ArrayAccess methods which allows
     * to access the request data as array indexes on the object.
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset))
            $this->pageInstance[] = $value;
        else
            $this->pageInstance[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->pageInstance[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->pageInstance[$offset]);
    }
    public function &offsetGet($offset) {
        return $this->pageInstance[$offset];
    }

    /**
     * Converts the object to array, returning the request data.
     */
    public function toArray() {
        return $this->pageInstance->toArray();
    }

    /**
     * Called by Selio before the main request method and after the page
     * instance's RequestStart method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onRequestStart() { return; }

    /**
     * Called by Selio on POST request method in place of the page instance's
     * method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onPOSTRequest() { return; }

    /**
     * Called by Selio on GET request method in place of the page instance's
     * method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onGETRequest() { return; }

    /**
     * Called by Selio on PUT request method in place of the page instance's
     * method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onPUTRequest() { return; }

    /**
     * Called by Selio on DELETE request method in place of the page instance's
     * method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onDELETERequest() { return; }

    /**
     * Called by Selio after the main request method and before the page
     * instance's RequestEnd method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onRequestEnd() { return; }

    /**
     * Gets the validation data.
     * Method is called by Selio.
     */
    public function getValidation() : array {
        return $this->validation;
    }
}