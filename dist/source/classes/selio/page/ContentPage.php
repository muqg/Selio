<?php
namespace selio\page;

use selio\Core;
use selio\SelioException;

/**
 * Class is meant to be used as a base for the
 * regular html-page classes.
 * @see \selio\page\AjaxPage for more information
 * on pages meant to handle ajax requests solely.
 */
abstract class ContentPage extends Page {
    /*
    * Event name constants.
    */
    const PAGE_INITIALIZATION_EVENT = 'pageInitialization';
    const PAGE_STYLESHEETS_EVENT = 'pageStylesheets';
    const PAGE_SCRIPTS_EVENT = 'pageScripts';
    const PAGE_CONTENT_EVENT = 'pageContent';
    const PAGE_FINALIZATION_EVENT = 'pageFinalization';


    /**
     * The type of the page.
     * @var string
     */
    private $type;

    /**
     * Holds the stylesheet files' data.
     * @var array
     */
    private $stylesheets = [];

    /**
     * Holds the script files' data.
     * @var array
     */
    private $scripts = [];

    /**
     * The title of the page. Used for the browser tags.
     * @var string
     */
    private $title = '';

    /**
     * Description of the page. Used for the browser tags.
     * @var string
     */
    private $description = '';

    /**
     * Holds the attributes for each tag allowed to have custom attributes.
     * @var array
     */
    private $tagAttributes = [
        'html' => ['lang' => SELIO_LANGUAGE],
        'body' => [],
        'mainContainer' => ['id' => 'mainContainer'],
        'contentContainer' => ['id' => 'contentContainer'],
    ];


    function __construct(string $pageName, string $type) {
        parent::__construct($pageName);
        $this->type = $type;
    }

    /**
     * Method is called on AJAX request to the page. Override in order to provide
     * functionality.
     * @see \selio\page\AjaxPage for more information for pages meant to handle
     * ajax requests solely.
     */
    public function AJAX() { return; }


    /**
     * This method is called by Selio and builds the final html response for the
     * page by calling other Selio hook methods and more.
     */
    public final function GET(...$urlArgs) {
        $routeComponent = $this->getRouteComponent();

        // Buffer discards any output during initialization.
        ob_start();
        $this->initialization(...$urlArgs);
        if($routeComponent)
            $routeComponent->initialize(...$urlArgs);
        $this->runEvent(self::PAGE_INITIALIZATION_EVENT);
        ob_end_clean();
        ?>
        <!DOCTYPE html>
        <html <?= Core::getAttributeString($this->tagAttributes['html']) ?>>
        <head>
            <meta charset="<?= setting('selio.page.charset') ?>">
            <meta name="viewport" content="width=device-width">
            <meta name="description" content="<?= $this->pageDescription() ?>">
            <title><?= $this->pageTitle() ?></title>
            <link rel="icon" href="/<?= setting('selio.page.iconPath') ?>">
            <?php
            $this->pageHead(...$urlArgs);
            $this->pageAssetsAndEvent('stylesheets');
            $this->pageAssetsAndEvent('scripts');
            ?>
        </head>
        <body <?= Core::getAttributeString($this->tagAttributes['body']) ?>>
            <div <?= Core::getAttributeString($this->tagAttributes['mainContainer']) ?>>
                <?php
                $this->pageHeader(...$urlArgs);
                // Content container div tag.
                echo '<div '. Core::getAttributeString($this->tagAttributes['contentContainer']) .'>';
                    $this->runEvent(self::PAGE_CONTENT_EVENT);
                    if($routeComponent)
                        $routeComponent->build(...$urlArgs);
                    else
                        $this->pageContent(...$urlArgs);
                echo '</div>';
                $this->pageFooter(...$urlArgs);
                echo $this->getJSEnvironmentElement();
                ?>
            </div>
        </body>
        </html>

        <?php
        // Buffer discards any output during finalization.
        ob_start();
        $this->runEvent(self::PAGE_FINALIZATION_EVENT);
        $this->finalization(...$urlArgs);
        if($routeComponent)
            $routeComponent->finalize(...$urlArgs);
        ob_end_clean();
    }

    /**
     * Returns the javascript environemnt element. It is required to be present on
     * the page when using selio.js' Selio.ENV constant.
     */
    private function getJSEnvironmentElement() : string {
        $env = json_encode([
            'urlPrefix' => SELIO_INSTALL_PATH !== '/' ? SELIO_INSTALL_PATH : '',
            'language' => setting('selio.multiLanguage') ? SELIO_LANGUAGE : null,
        ]);
        return '<i style="display: none;" id="selio_js_env">' . $env . '</i>';
    }


    /**
     * This method is called by Selio before any other hook method and should
     * be used to perform any initiallization. Any output output is discarded.
     *
     * Method is provided with the URL arguments.
     */
    protected function initialization() { return; }

    /**
     * Method is called by Selio and should return a string representing the
     * page title that is output within an html <title> element in the page's head.
     */
    protected function pageTitle() : string {
        $title = $this->getTitle();
        $title = ($title ? $title . ' - ' : '') . setting('website');
        return $title;
    }

    /**
     * Method is called by Selio and should return a string representing the
     * page's meta description.
     */
    protected function pageDescription() : string {
        return $this->getDescription();
    }

    /**
     * This method is called by Selio within the head element and is meant to
     * provide any additional meta tags and more. For scripts and stylesheets
     * refer to ContentPage::addScript() and ContentPage::addStylesheet()
     * respectively.
     *
     * Method is provided with the URL arguments.
     */
    protected function pageHead() { return; }

    /**
     * Method is called by Selio and page's header html should be generated
     * within this method.
     *
     * Method is provided with the URL arguments.
     */
    protected function pageHeader() { return; }

    /**
     * Method is called by Selio and page's main html content should be generated
     * within this method.
     *
     * Method is provided with the URL arguments.
     */
    protected function pageContent() { return; }

    /**
     * Method is called by Selio and page's footer html should be generated
     * within this method. Extending this
     *
     * Method is provided with the URL arguments.
     */
    protected function pageFooter() { return; }

    /**
     * This method is called by Selio and after any other hook method and
     * page code execution. It should be used to perform any finalization
     * and explicit cleanup. Any output output is discarded.
     *
     * Method is provided with the URL arguments.
     */
    protected function finalization() { return; }


    /**
     * Appends a stylesheet to the list of stylesheets for the page. This list
     * is later used to output stylesheet link elements within the page's head.
     * @param string $name The fully qualified name of the stylesheet as a local
     * path relative to the default stylesheet assets path. Name can also be a
     * url to a stylesheet from another domain.
     * @param array $attributes The attributes that will be added to the element.
     * The array keys represent the attributes while the array values are values
     * of their respective attribute.
     * Numeric keys' values will be applied as attributes without value.
     * @param string $appendQuery Allows to append a specific string as a query
     * to the end of the url to a local file thus allowing to provide additional
     * data with the url or version the cached file on the client side.
     * - The default value of 'mtime' appends the last modified time.
     */
    public final function addStylesheet(string $name, array $attributes = [], string $appendQuery = 'mtime') {
        $this->stylesheets[$name] = [
            'name' => $name,
            'attributes' => $attributes,
            'appendQuery' => $appendQuery
        ];
    }
    /**
     * Appends a script to the list of scripts for the page. This list is later
     * used to output script elements within the page's head.
     * @param string $name The fully qualified name of the script as a local path
     * relative to the default script assets path. Name can also be a url to a
     * script from another domain.
     * @param array $attributes The attributes that will be added to the element.
     * The array keys represent the attributes while the array values are values
     * of their respective attribute.
     * Numeric keys' values will be applied as attributes without value.
     * @param string $appendQuery Allows to append a specific string as a query
     * to the end of the url to a local file thus allowing to provide additional
     * data with the url or version the cached file on the client side.
     * - The default value of 'mtime' appends the last modified time.
     */
    public final function addScript(string $name, array $attributes = ['defer'], string $appendQuery = 'mtime') {
        $this->scripts[$name] = [
            'name' => $name,
            'attributes' => $attributes,
            'appendQuery' => $appendQuery
        ];
    }

    /**
     * A utility method that allows the addition of multiple stylesheets at once.
     * It accepts a variable number of arguments where each stylesheet is
     * represented by its arguments in valid array format or its string name if
     * no other data has to be passed.
     * @see ContentPage->addStylesheet() for more information.
     */
    public final function addStylesheets(...$stylesheetData) {
        $this->addAssets('css', $stylesheetData);
    }
    /**
     * A utility method that allows the addition of multiple scripts at once.
     * It accepts a variable number of arguments where each script is represented
     * by its arguments in valid array format or its string name if no other
     * data has to be passed.
     * @see ContentPage->addScript() for more information.
     */
    public final function addScripts(...$scriptsData) {
        $this->addAssets('js', $scriptsData);
    }
    /**
     * A common method for the addition of multiple assets of the specified type
     * thta can be either 'js' or 'css'.
     */
    private function addAssets(string $type, array $assetsData) {
        foreach($assetsData as $data) {
            $name = is_string($data) ? $data : $data['name'];
            $attributes = $data['attributes'] ?? [];
            $appendQuery = $data['appendQuery'] ?? 'mtime';

            if($type === 'css')
                $this->addStylesheet($name, $attributes, $appendQuery);
            else if ($type === 'js')
                $this->addScript($name, $attributes, $appendQuery);
            else
                throw new SelioException("Type must be either css or js, $type given.");
        }
    }

    /**
     * Runs asset's event and then builds and outputs asset elements.
     * @param string $type The asset type -> 'stylesheets' or 'scripts'.
     */
    private function pageAssetsAndEvent(string $type) {
        $eventName = 'page' . ucfirst($type);
        $event = $this->runEvent($eventName, $this->$type);
        $this->$type = $event->data;
        $this->$eventName();
    }
    /**
     * Builds and outputs the stylesheet link elements.
     * @see ContentPage->addStylesheet() for more information.
     */
    private function pageStylesheets() {
        $defaultStylesheet = $this->type . '/' . $this->getPageName() . '.min';
        // Append the default stylesheet.
        if(setting('selio.page.loadDefaultAssets')) {
            $this->addStylesheet($defaultStylesheet);
        }
        foreach($this->stylesheets as $data) {
            $log = $data['name'] !== $defaultStylesheet;
            $this->buildAssetElement('css', $data, $log);
        }
    }
    /**
     * Builds and outputs the script elements.
     * @see ContentPage->addScript() for more information.
     */
    private function pageScripts() {
        $defaultScript = $this->type . '/' . $this->getPageName()  . '.min';
        // Append the default script.
        if(setting('selio.page.loadDefaultAssets')) {
            $this->addScript($defaultScript, ['defer']);
        }
        foreach($this->scripts as $data) {
            $log = $data['name'] !== $defaultScript;
            $this->buildAssetElement('js', $data, $log);
        }
    }
    /**
     * Outputs an html script or stylesheet element while appending appropriate
     * query string to the end if it is not an external link and logs on failure
     * to locate a local file.
     * @param string $type The type of the element; Accepts 'css' and 'js' values.
     * @param array $data The data for the element containing its name (or link),
     * attributes array and query to append.
     * @param boolean $log Whether to log on failure to locate a local file.
     */
    private function buildAssetElement(string $type, array $data, bool $log) {
        // Extracts $name, $attributes and $appendQuery.
        extract($data);

        $attributes = Core::getAttributeString($attributes);
        $isLink = strpos($name, 'http://') === 0 || strpos($name, 'https://') === 0;

        if(!$isLink) {
            $filename = $data['name'] . '.' . $type;
            // This also checks if the file exists.
            $mtime = @filemtime($this->getAssetsPath("$type/$filename"));
            if(!$mtime) {
                if($log)
                    $this->log("[Page] Unable to locate \"$filename\" for page \"{$this->getPageName()}\"");
                // Cancel asset building if file does not exist.
                return;
            }
            $fileSource = $this->getAssetsURI("$type/$filename");
            if($appendQuery)
                $fileSource .= '?' . ($appendQuery === 'mtime' ? $mtime : $appendQuery);
        }
        else {
            $fileSource = $name;
        }

        if($type === 'css')
            echo '<link rel="stylesheet" href="'. $fileSource. '" '. $attributes .'>';
        else if($type === 'js')
            echo '<script src="'. $fileSource. '" '. $attributes .'></script>';
        else
            throw new SelioException("Type must be either css or js, $type given.");
    }


    /*
     * The following setTagAttributes methods are meant to be used as setters
     * for the attributes of default tags allowed to have custom ones added.
     * @param array $attributes The keys represent the attribute
     * and the values represent the value of the attribute. Existing
     * attribute values will be ovewritten.
     */
    protected final function setHTMLTagAttributes(array $attributes) {
        $this->setTagAttributes('html', $attributes);
    }
    protected final function setBodyTagAttributes(array $attributes) {
        $this->setTagAttributes('body', $attributes);
    }
    protected final function setContentContainerTagAttributes(array $attributes) {
        $this->setTagAttributes('contentContainer', $attributes);
    }
    protected final function setMainContainerTagAttributes(array $attributes) {
        $this->setTagAttributes('mainContainer', $attributes);
    }
    private function setTagAttributes(string $tagName, array $attributes) {
        foreach($attributes as $key => $val)
            $this->tagAttributes[$tagName][$key] = $val;
    }

    /**
     * Gets the page title.
     */
    protected final function getTitle() : string {
        return $this->title;
    }
    /**
     * Sets the page title.
     * @param string $title Page's title.
     */
    public final function setTitle(string $title) {
        $this->title = $title;
    }

    /**
     * Gets the page description.
     */
    protected final function getDescription() : string {
        return $this->description;
    }
    /**
     * Sets the page description
     * @param string $description Page's description.
     */
    public final function setDescription(string $description) {
        $this->description = $description;
    }
}