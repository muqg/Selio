<?php
namespace pages\selio;

use selio\Core;

final class Authentication extends \selio\page\AuthenticationPage {

    /**
     * This is the default authentication method used by Selio's built in classes.
     * Create a custom authentication class and set it to be the authentication
     * page in the settings in order to override authentication.
     */
    public static function authenticate(string $authUser = '', string $authPass = '') {
        $authFile = Core::getIncludePath('.htpasswd');
        if(!file_exists($authFile))
            return false;

        if($_SESSION['admin_auth'] ?? null || (!$authUser || !$authPass)) {
            $authUser = $_SESSION['admin_auth']['user'] ?? '';
            $authPass = $_SESSION['admin_auth']['pass'] ?? '';
        }

        $authData = explode(PHP_EOL, file_get_contents($authFile));
        foreach($authData as $data) {
            list($user, $passhash) = explode(':', $data);

            if(trim($user) === trim($authUser)
                && password_verify($authPass, trim($passhash)))
            {
                $_SESSION['admin_auth']['user'] = $authUser;
                $_SESSION['admin_auth']['pass'] = $authPass;
                return true;
            }
        }
        return false;
    }


    // ===========================


    protected function initialization() {
        $this->addScript('selio/selio.min', ['defer']);
        $this->addScript('selio/intern/auth', ['defer']);
    }

    protected function pageHead() {
        ?>
        <style>
            div {
                margin: 10px auto;
                text-align: center;
            }

            h1 {
                padding-top: 27px;
                text-align: center;
            }
        </style>
        <?php
    }

    protected function pageContent() {
        ?>
        <h1>Authentication required</h1>
        <div>
            <input id="username" type="text" placeholder="username" autofocus>
        </div>
        <div>
            <input id="password" type="password" placeholder="password">
        </div>
        <div>
            <button type="button" id="button">Confirm</button>
        </div>
        <?php
    }
}