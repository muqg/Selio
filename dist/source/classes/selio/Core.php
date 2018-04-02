<?php
namespace selio;

/**
 * The Core class that runs Selio and provides static utility methods that are
 * related to Selio's general structure and behaviour.
 * Call Core::run() to start the fun part.
 */
final class Core {
    /**
     * Name of the front page class.
     */
    const FRONT_PAGE = 'front';

    /**
     * The current position of the page name in the URL arguments.
     */
    private static $pageArgumentPosition = 0;

    /**
     * The determined name of the page.
     */
    private static $pageName = '';

    /**
     * Current namespace of the page class.
     */
    private static $namespace = '';

    /**
     * The URL arguments that will be provided to the page and its hook methods
     * as a variable-length list of arguments.
     */
    private static $additionalArguments = [];

    /**
     * Whether Selio's core is already running or not.
     */
    private static $isRunning = false;

    /**
     * Method is used to initially run Selio and start rockin'.
     */
    public static function run() {
        $start = setting('selio.monitorExecutionTime') ? microtime(true) : null;

        if(!self::$isRunning) {
            self::$isRunning = true;

            session_start();
            header_remove('X-Powered-By');

            $requestURI = parse_url($_SERVER['REQUEST_URI'] ?? $_SERVER['REDIRECT_URI']);
            $installPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', SELIO_ROOT) ? : '/';
            // Remove install path from the URL.
            if(strpos($requestURI['path'], $installPath) === 0) {
                $requestURI['path'] = substr(
                    $requestURI['path'],
                    strlen($installPath)
                );
            }
            // Explode URL then filter empty entries and re-index.
            $args = array_values(array_filter(explode('/', $requestURI['path'])));
            $isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? false)
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            $lang = setting('selio.defaultLanguage');
            if(setting('selio.multiLanguage')) {
                self::$pageArgumentPosition = 1;
                $lang = self::determineLanguage($args, $requestURI['path'], $installPath);
            }

            self::processArgs($args);

            define('SELIO_LANGUAGE', $lang);
            define('SELIO_AJAX', $isAjax);
            define('SELIO_REQUEST_PATH', $requestURI['path']);
            define('SELIO_REQUEST_ARGS', $args);
            define('SELIO_INSTALL_PATH', $installPath);

            self::executePage($isAjax, $requestURI);
        }

        if($start !== null) {
            $end = round((microtime(true) - $start) * 1000);
            if($end > setting('selio.executionTime'))
                self::log('PHP execution time for path ' . implode('/', $requestURI) . " was $end ms");
        }
    }

    /**
     * Determines the current language for multi-language enabled pages.
     */
    private static function determineLanguage(array $args, string $requestPath, string $installPath) {
        $lang = $args[0] ?? '';
        $defaultLanguage = setting('selio.defaultLanguage');

        // Allows access to admin and ajax pages without requiring a language
        // to be passed in the URL explicitly.
        if($lang === 'admin' || $lang === 'ajax') {
            $lang = '';
            self::$pageArgumentPosition = 0;
        }

        if(!in_array($lang, setting('selio.languages'))) {
            // Attempt to determine language from headers when no path is passed.
            if($lang === '') {
                if(setting('selio.autoDetermineLanguage'))
                    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '', 0, 2);
                if(!in_array($lang, setting('selio.languages')))
                    $lang = $defaultLanguage;
            }
            else {
                // Redirect() method is not usable at this point yet and thus
                // error has to be handled manually.
                self::logFlagged(
                    "Invalid language argument '$lang' to default one '$defaultLanguage'",
                    'Redirect'
                );
                http_response_code(404);
                $install = $installPath !== '/' ? $installPath : '';
                $requestPath = str_replace($lang, $defaultLanguage, $requestPath);
                header('Location: ' . $install . "/$requestPath");
                exit("Invalid language $lang");
            }
        }
        return $lang;
    }

    /**
     * Processes the URI arguments and instantiates the appropriate fields.
     */
    private static function processArgs(array $args) {
        $pageName = $args[self::$pageArgumentPosition] ?? self::FRONT_PAGE;
        if($pageName === 'ajax' || $pageName === 'admin') {
            $namespace = $pageName;
            $pageName = $args[self::$pageArgumentPosition + 1] ?? self::FRONT_PAGE;
            $additionalArgumentsIndex = self::$pageArgumentPosition + 2;
        }
        else {
            $namespace = 'pub';
            $additionalArgumentsIndex = self::$pageArgumentPosition + 1;
        }
        self::$pageName = $pageName;
        self::$namespace = $namespace;
        self::$additionalArguments = array_slice($args, $additionalArgumentsIndex);
    }

    /**
     * Creates a page instance based on the split URI data.
     */
    private static function createPageInstance(array $requestURI) {
        $instance = null;
        try {
            $classname = self::$namespace !== 'ajax' ? 'pages' : '';
            $pageName = str_replace('_', '', ucwords(self::$pageName, '_'));
            $classname = $classname . '\\' . self::$namespace . '\\' . $pageName;
            $instance = new $classname(self::$pageName);
        }
        catch(ClassException $ex) {
            self::redirectToFrontPage($requestURI);
        }
        return $instance;
    }

    /**
     * Executes the appropriate hook methods on the current page instance.
     */
    private static function executePage(bool $isAjax, array $requestURI) {
        $page = self::createPageInstance($requestURI);
        if($page->authenticatePage(...self::$additionalArguments)) {
            $page->routePage(...self::$additionalArguments);
            if($isAjax)
                $page->AJAX(...self::$additionalArguments);
            else
                $page->GET(...self::$additionalArguments);
        }
        else {
            $authPage = setting('selio.authenticationPage');
            if($isAjax)
                (new $authPage)->AJAX(...self::$additionalArguments);
            else
                (new $authPage)->GET(...self::$additionalArguments);
        }
    }

    /**
     * Redirects to the front page while preventing infinite redirect to it on error.
     */
    private static function redirectToFrontPage(array $requestURI) {
        if(self::$pageName === self::FRONT_PAGE) {
            // Prevents infinite redirects to front page in case of an error.
            throw new SelioException('Error loading front page');
        }
        else {
            $message = $requestURI['path'] .
                ($requestURI['query'] ?? null ? '?'.$requestURI['query'] : '')
                . ' to front page';
            self::redirect('/', $message);
        }
    }



    // ===========================



    /**
     * Logs a message and the backtrace to where it occured.
     * @param string $message The message to be saved in the log file.
     * @param integer $backtrace A positive backtrace index to where the
     * message occured. A negative index does not log backtrace at all.
     */
    public static function log(string $message, int $backtrace = -1) {
        $logFile = setting('selio.logFile');

        $backtraceText = '';
        if($backtrace >= 0) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)[$backtrace];
            if($trace)
                $backtraceText = "in file {$trace['file']} on line {$trace['line']}";
        }
        $contents = '[' . date('j, M Y H:i:s') . "] $message $backtraceText" . PHP_EOL;
        file_put_contents($logFile, $contents, FILE_APPEND);
    }

    /**
     * Logs a message with a flag if it is not within the settings' log ignore
     * list and the back trace to where it occured.
     * @param string $message The message to be saved in the log file.
     * @param string $flag The flag to be applied to the message.
     * @param integer $backtrace A positive backtrace index to where the
     * message occured. A negative index does not log backtrace at all.
     */
    public static function logFlagged(string $message, string $flag, int $backtrace = -1) {
        $ignore = setting('selio.logIgnoreFlags');
        if($ignore === 'all' || in_array(strtolower($flag), $ignore))
            return;

        $message = "[$flag] $message";
        self::log($message, $backtrace);
    }

    /**
     * Returns a Selio URI. This consists of a path, relative to website root,
     * with prepended install path and language, based on the current environment
     * constants.
     * @param string $path The target URI path.
     */
    public static function getURI(string $path = '') : string {
        $installPath = SELIO_INSTALL_PATH !== '/' ? SELIO_INSTALL_PATH : '';
        $lang = setting('selio.multiLanguage') ? '/' . SELIO_LANGUAGE : '';
        return $installPath . $lang . $path;
    }

    /**
     * Gets the path to the include folder appending any additional path
     * string provided as a parameter.
     * @param string $path Optional path string to be appended.
     */
    public static function getIncludePath(string $path = '') : string {
        return SELIO_ROOT . '/source/include' . ($path ? "/$path" : '');
    }

    /**
     * Gets an assets URI while appending the provided path parameter.
     * @param string $path Optional path string to be appended to the assets URI.
     */
    public static function getAssetsURI(string $path = '') : string {
        $install = SELIO_INSTALL_PATH;
        return ($install !== '/' ? $install : '') . '/assets' . ($path ? "/$path" : '');
    }

    /**
     * Gets the local assets path (server path) while appending the provided
     * path parameter.
     * @param string $path Optional path string to be appended to the assets path.
     */
    public static function getAssetsPath(string $path = '') : string {
        return SELIO_ROOT . '/assets' . ($path ? "/$path" : '');
    }

    /**
     * Builds a string representation of html node attributes from an array.
     * @param array $attributes The array keys are the attribute names and the
     * array values are the attribute values.
     * An array entry with numeric key (only value) represents an attribute
     * with no value (empty attribute).
     */
    public static function getAttributeString(array $attributes) : string {
        foreach($attributes as $key => $val)
            $tagAttributes[] = !is_int($key) ? "$key=\"$val\"" : $val;
        return implode(' ', $tagAttributes ?? []);
    }

    /**
     * Redirects to a location with a response code, logs a message
     * and exits execution.
     * @param string $location The header location to redirect to.
     * @param string $message The message to log and exit with.
     * @param integer $responseCode The HTTP response code.
     */
    public static function redirect(string $location, string $message, int $responseCode = 301) {
        http_response_code($responseCode);
        self::logFlagged($message, 'Redirect');
        header('Location: ' . self::getURI($location));
        exit($message);
    }
}