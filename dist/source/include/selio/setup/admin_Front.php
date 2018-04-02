<?php
namespace pages\admin;

class Front extends \selio\page\AdminPage {
    protected function pageContent() {
        echo '<h1>Welcome to ' . setting('website') . ' administration panel!</h1>';
    }
}