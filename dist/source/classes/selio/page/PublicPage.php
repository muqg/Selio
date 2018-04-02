<?php
namespace selio\page;

/**
 * Class should be extended by any public page class.
 */
abstract class PublicPage extends ContentPage {

    function __construct(string $pageName) {
        parent::__construct($pageName, 'pub');
    }

}