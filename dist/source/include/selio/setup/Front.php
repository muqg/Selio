<?php
namespace pages\pub;

class Front extends \selio\page\PublicPage {
    protected function initialization() {
        // Page initialization goes here.
    }

    protected function pageContent() {
        // Page content goes here.
        echo '<h1>Welcome to Selio ' . SELIO_VERSION . '!</h1>';
    }
}