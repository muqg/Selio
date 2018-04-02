<?php
namespace selio\page;

/**
 * Class is meant to be extended by the authentication page for the website.
 * It's routing and authentication are not called by Selio's API and therefore
 * should not be used for these pages.
 */
abstract class AuthenticationPage extends ContentPage {
    function __construct() {
        parent::__construct('Authentication', 'auth');
        $this->setTitle('Authentication');
    }

    /**
     * This is the method that should perform the authentication. It should
     * return a truthy value on success, and falsey on failure to authenticate
     * request. The return value is set to the authenticationData property and
     * thus typically an array should be returned. Override in order to provide
     * authentication functionality.
     *
     * Method is provided with the URL arguments.
     */
    public static function authenticate() {
        return true;
    }
}