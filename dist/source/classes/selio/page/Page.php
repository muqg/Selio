<?php
namespace selio\page;

use selio\Core;

/**
 * Classes aiming to use PageComponent should
 * inherit from Page in order to allow components
 * to locate and hold the reference to the class (page)
 * and use its database connection if any.
 */
abstract class Page extends \selio\Base {
    /**
     * Whether authentication will be required for the request to the page.
     * Override to change behaviour.
     * @var boolean
     */
    protected $auth = false;

    /**
     * Array representation of the page routes as (url => componentPath) pairs.
     * Override this property in its corret format to provide router functionality.
     * See Page::routePage() for more information.
     * @var array
     */
    protected $routes = [];

    /**
     * Page singleton object, also known as the 'main' page object.
     * @var Page
     */
    private static $instance = null;

    /**
     * The name of the page. Typically it is the URL argument representation of
     * the page class' name (first or second URL argument).
     * @var string
     */
    private $name = '';

    /**
     * Router control property. Used to disallow duplicate routing.
     * @var boolean
     */
    private $isRouted = false;

    /**
     * Holds the route component object instance if page is routed.
     * @var Component
     */
    private $routeComponent = null;

    /**
     * Authentication control property. Used to disallow duplaicate authentication.
     * @var boolean
     */
    private $isAuthenticated = false;

    /**
     * Holds authentication data for current request. Typically it should be an
     * array but can also be a value of any type. Any value that evaluates to
     * TRUE singifies that authentication is successful; Any value that evaluates
     * to FALSE loads the authentication component.
     * @var mixed
     */
    private $authenticationData = null;

    function __construct(string $pageName = '') {
        // Explicitly enable automatic database connection.
        parent::__construct(true);

        $this->name = $pageName ? : get_class($this);
        /*
        * Does not allow static $instance to be overwritten if
        * another instance of a Page derived class is created.
        * This way static $instance will only be set the first
        * time a page has been instantiated.
        */
        if(!self::$instance)
            self::$instance = $this;
    }

    /**
     * Called by Selio's API to authenticate user's access to the page.
     * @see AuthenticationPage::authenticate() for more information on authentication.
     */
    public final function authenticatePage(...$urlArgs) : bool {
        if(!$this->auth)
            return true;
        else if($this->isAuthenticated)
            return (bool)$this->authenticationData;

        $authPage = setting('selio.authenticationPage');
        $this->authenticationData = $authPage::authenticate(...$urlArgs);
        $this->isAuthenticated = true;

        $isSuccessful = (bool)$this->authenticationData;
        if(!$isSuccessful)
            self::$instance = null;
        return $isSuccessful;
    }


    /**
     * Called by Selio's API to determine URL's route to the page, based on the
     * routes class property.
     * @see Page::$routes for more information about routing.
     */
    public final function routePage(...$urlElements) {
        if($this->isRouted)
            return;
        $this->isRouted = true;

        $componentPath = null;
        if($urlElements) {
            $urlLength = count($urlElements);
            $any = '*';
            foreach($this->routes as $routeURL => $routePath) {
                $routeElements = explode('/', $routeURL);
                $routeLength = count($routeElements);
                if($urlLength !== $routeLength)
                    continue;
                // Indicates whether elements in URL and route match so far.
                $isMatch = true;
                for($i = 0; $i < $routeLength && $isMatch; $i++) {
                    $urlArg = $urlElements[$i];
                    $routeArg = $routeElements[$i];
                    if($routeArg !== $urlArg && $routeArg !== $any)
                        $isMatch = false;
                }

                if($isMatch) {
                    $componentPath = $routePath;
                    break;
                }
            }
            // Redirect to no URL elements if component path is not matched thus far.
            if(!$componentPath) {
                // Redirect method itself will pass the language.
                if(setting('selio.multiLanguage'))
                    $urlElements[] = SELIO_LANGUAGE;

                $loc = implode('/', array_diff(SELIO_REQUEST_ARGS, $urlElements));
                $message = 'invalid route path for page ' . $this->getPageName()
                    . ' and uri ' . SELIO_REQUEST_PATH;
                Core::redirect("/$loc", $message);
            }
        }
        // On no URL elements attempt to locate default route path.
        else if($this->routes['default'] ?? false) {
            $componentPath = $this->routes['default'];
        }

        if($componentPath) {
            $component = 'routes\\' . strtr($componentPath, '/', '\\');
            $this->routeComponent = new $component();
        }
    }

    /**
     * Returns the page singleton object, also known as the 'main' page object.
     */
    public final static function getPageInstance() : Page {
        return self::$instance;
    }

    /**
     * Returns the page name.
     */
    public final function getPageName() : string {
        return $this->name;
    }

    /**
     * Returns the component object the page is routed to or NULL if not routed.
     */
    public final function getRouteComponent() {
        return $this->routeComponent;
    }

    /**
     * Returns the authentication data property.
     */
    public final function getAuthenticationData() {
        return $this->authenticationData;
    }
}
