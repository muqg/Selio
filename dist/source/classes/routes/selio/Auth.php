<?php
namespace routes\selio;

use pages\selio\Authentication;


class Auth extends \selio\page\AjaxComponent {
    protected $validation = [
        'any' => ['user', 'pass'],
    ];

    protected function onPOSTRequest() {
        echo (bool)Authentication::authenticate($this['user'], $this['pass']);
    }
}