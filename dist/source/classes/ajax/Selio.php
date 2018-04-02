<?php
namespace ajax;

final class Selio extends \selio\page\AjaxPage {
    protected $routes = [
        'auth' => 'selio/Auth',
    ];
}