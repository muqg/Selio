<?php
namespace selio\page;
use ArrayAccess;
use selio\Core;

/**
 * Class is meant to be used to handle AJAX requests similarly to REST and
 * other such APIs.
 */
abstract class AjaxPage extends Page implements ArrayAccess {
    /**
     * Methods supported by Selio's AJAX API.
     */
    const SUPPORTED_METHODS = ['POST', 'PUT', 'GET', 'DELETE'];

    /**
     * Holds the keys that should be present in the reuqest data for each method.
     * Override this property in order to provide validation functionality.
     * @see AjaxPage::isDataValid() for more information.
     * @var array
     */
    protected $validation = [];

    /**
     * Current request's method.
     * @var string
     */
    private static $method = '';

    /**
     * Current request's combined data. It is accessed by using array access on
     * the object.
     * @var array
     */
    private $requestData = [];

    /**
     * This method is called by Selio handles non-AJAX request to this page by
     * logging an error and redirecting to front page.
     * @see \selio\page\PublicPage or \selio\page\AdminPage for pages meant to
     * display content.
     */
    public final function GET(...$urlArgs) {
        $message = 'Non-ajax request to AJAX page '. $this->getPageName() .'.php'
            . ' The request headers should have HTTP_X_REQUESTED_WITH set to xmlhttprequest in any letter case.'
            . ' Check the documentation for more information.';
        Core::redirect('/', $message, 406);
    }

    /**
     * This method is called by Selio and handles the AJAX request by calling
     * the appropriate request method handler.
     */
    public final function AJAX(...$urlArgs) {
        $this->identifyRequestData();
        $this->determineMethod();

        $routeComponent = $this->getRouteComponent();
        if($this->isDataValid()) {
            $classMethod = 'on' . $this->getMethod() . 'Request';
            $this->onRequestStart(...$urlArgs);

            if($routeComponent)
                $routeComponent->build(...$urlArgs);
            else
                $this->$classMethod(...$urlArgs);

            $this->onRequestEnd(...$urlArgs);
        }
        else {
            http_response_code(400);
            $this->log(
                '[Page] Unable to validate request data for AJAX page ' . $this->getPageName()
                . ' and method ' . $this->getMethod()
            );
        }
    }

    /**
     * Identifies the combined request data.
     */
    private function identifyRequestData() {
        $data = array_merge($_POST, $_GET);

        $input = file_get_contents('php://input') ?? [];
        $otherData = json_decode($input, true);
        if(json_last_error() !== JSON_ERROR_NONE)
            parse_str($input, $otherData);

        if(is_array($otherData))
            $data = array_merge($data, $otherData);
        $this->requestData = $data;
    }

    /**
     * Determines the current reuqest method.
     */
    private function determineMethod() {
        if($this->getMethod())
            return;

        $method = strtoupper($this['request_method'] ?? $_SERVER['REQUEST_METHOD']);
        if(in_array($method, self::SUPPORTED_METHODS)) {
            self::$method = $method;
            unset($this['request_method']);
        }
        else {
            $message = "{$method} method is not supported for {$this->getPageName()} AJAX page.";
            Core::redirect('/', $message, 405);
        }
    }

    /**
     * Validates the request data against the provided validation property in
     * its correct format. If page is routed, the component's validation is
     * used instead.
     */
    private function isDataValid() : bool {
        $routeComponent = $this->getRouteComponent();
        $validation = $routeComponent ? $routeComponent->getValidation() : $this->validation;

        $validationData = array_merge(
            $validation[$this->getMethod()] ?? [],
            $validation['any'] ?? []
        );
        foreach($validationData as $val) {
            if(array_key_exists($val, $this->requestData))
                continue;
            return false;
        }
        return true;
    }

    /*
     * Implementation for the ArrayAccess methods which allows
     * to access the request data as array indexes on the object.
     */
    public function offsetSet($offset, $value) {
        if (is_null($offset))
            $this->requestData[] = $value;
        else
            $this->requestData[$offset] = $value;
    }
    public function offsetExists($offset) {
        return isset($this->requestData[$offset]);
    }
    public function offsetUnset($offset) {
        unset($this->requestData[$offset]);
    }
    public function &offsetGet($offset) {
        return $this->requestData[$offset];
    }

    /**
     * Converts the object to array, returning the request data.
     */
    public function toArray() : array {
        return $this->requestData;
    }

    /**
     * Called by Selio before the main request method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onRequestStart() { return; }

    /**
     * Called by Selio on POST request method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onPOSTRequest() { return; }

    /**
     * Called by Selio on GET request method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onGETRequest() { return; }

    /**
     * Called by Selio on PUT request method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onPUTRequest() { return; }

    /**
     * Called by Selio on DELETE request method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onDELETERequest() { return; }

    /**
     * Called by Selio after the main request method.
     *
     * Method is provided with the URL arguments.
     */
    protected function onRequestEnd() { return; }


    /**
     * Gets the current request method.
     */
    public final function getMethod() : string {
        return self::$method;
    }
}
