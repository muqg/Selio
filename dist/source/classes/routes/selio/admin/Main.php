<?php
namespace routes\selio\admin;

use selio\page\PageComponent;


final class Main extends PageComponent {
    public function build() {
        echo '<h1> Selio configuration panel.</h1>';
    }
}