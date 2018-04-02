## Content
- [Installation](#installation)
- [General functionality](#general-functionality)
- [First steps](#first-steps)
- [Localization](#localization)
- [Databases](#databases)
- [Components](#components)
- [Routing](#routing)
- [Caching](#caching)
- [AJAX](#ajax)
- [Authentication](#authentication)
- [Administration](#administration)

# Installation
Download the [source](https://github.com/muqg/Selio) and move all contents from the `dist` folder to your website root folder. Then execute the `setup.php` script which performs any installation steps. Then navigate to `*/source/include` path where you will find:
- `settings.php` where you can manually manage settings.
- `db.php` containing database connections information in format initially presented in the file. You should fill in any database information for database connections that you would like to use. This can be changed at any time.
- `.htpasswd` containing login information for the administration. The login information is in the same format as traditional `.htpasswd` files `username:password_hash` and is a temporary built-in method for [authentication](#authentication). The difference is that this file's password is encoded using [password_hash](http://php.net/manual/en/function.password-hash.php) function instead of [md5](https://en.wikipedia.org/wiki/MD5) which is traditionally used in `.htpasswd`. Default username is `selio` and password is `admin`. Feel free to manually change the username and password  so that they suit you.

Also make sure to check Selio's .htaccess file and modify it to suit your needs.

**Selio currently supports PHP 7.0+**

> For information about updating, check out the [readme](https://github.com/muqg/Selio/blob/master/README.md#updating).

# General functionality
Selio's projects have a strict project structure. After the installation you end up with a `.htaccess`, `index.php` and two folders: `source` and `assets`. The `.htaccess` file should redirect everything to the index.php file and pass (PT option) the URL unchanged for further processing by the controller which is run inside the `index.php` file by calling `Core::run()`.

The controller parses the URL which based on the options contains an __optional__ install *directory prefix* (since Selio can be installed in any directory without interfering with any other projects), __optional__ localization parameter, followed by a page name and then a variable number of arguments, separated by a slash, which are used for [routing](#routing) and as extra parameters to the default hook methods provided by Selio's API (read below...). An example URL would look like:

```
http://example.com/[prefix]/[lang]/page_name/[more]/[args]
```

The only mandatory part is the page name. The page name can be either a single word that will be capitalized or more than one word separated by underscores, each of which will be capitalized. Classes use [PascalCase](https://msdn.microsoft.com/en-us/library/x2dbyw72(v=vs.71).aspx), which means that a page called `front_page` will then load a class `FrontPage.php`.

The prefix is automatically determined by the controller, based on the difference between the `$_SERVER['DOCUMENT_ROOT']` and the directory containing the `index.php` file.

The necessity for the language parameter depends on the settings in the `*/source/include/settings.php` file.

The additional arguments cannot be passed by default and require a [routing](#routing) to be set up (see below).

The project folder structure is as follows:

```
root/
    assets/
        js/
        css/
        ...
    source/
        classes/
            ajax/
            components/
            pages/
                admin/
                pub/
            routes/
            selio/
        include/
            BaseExtension.php
            db.php
            settings.php
        init.php
    .htaccess
    index.php
```

Most of the directory meaning will be explained below. Any files within a folder named `selio` and classes called `Selio.php` and in addition the files index.php and init.php should not be modified since changes will be lost on update and unexpected behaviour may occur. You are free to use the files as provided in any way you find possible and suitable. Extensibility is provided almost everywhere it is needed.

Selio's autoloader loads classes from within the `*/source/classes` folder and Selio's own classes are namespaced inside the `*/source/classes/selio` folder (the \selio namespace). Classes below will be written relative to the `*/source/classes` folder with their respective namespace.

# First steps
Every extensible Selio class derives from `Base.php`, which is a class that wraps around static methods from other classes and provides base functionality for all Selio classes. It can be extended by adding methods to the trait located in `*/source/include/BaseExtension.php`.

The first and perhaps, most important class is the `\selio\page\PublicPage` class which allows for the creation of public pages. Public pages should extend this class or a class that extends it and should be placed inside `\pages\pub` namespace. For example the default page if a page name is not specified in the URL is the `\pages\pub\Front`. A URL request to a public page is as was shown above:

```
http://example.com/[prefix]/[lang]/page_name/[more]/[args]
```

All page classes should use [PascalCase](https://msdn.microsoft.com/en-us/library/x2dbyw72(v=vs.71).aspx) and the provided `page_name` is split on underscore and then each word is capitalized to form a valid class name.

Public pages provide a series of methods (coming from ContentPage class) that can be extended to provide functionality:

```PHP
abstract class PublicPage extends ContentPage {
    protected function initialization(...$args) {
        // Performs page initialization, including stylesheets,
        // scripts, initializing components, etc.
        // Its output is discarded.
    }
    protected function pageTitle(...$args) : string {
        // Returns the page's title.
    }
    protected function pageDescription(...$args) : string {
        // Returns the page's description.
    }
    protected function pageHead(...$args) {
        // Outputs additional html elements for within the head element.
    }
    protected function pageHeader(...$args) {
        // Outputs the page's header.
    }
    protected function pageContent(...$args) {
        // Outputs the page's content.
    }
    protected function pageFooter(...$args) {
        // Outputs the page's footer.
    }
    protected function finalization(...$args) {
        // Performs any finalization taksts. Its output is discared.
    }
}
```

These methods are called in the order presented above and are all provided with the additional URL arguments, following the page name. `initialization()` and `finalization()` differ from the rest in that their output is discarded. `pageTitle()` and `pageDescription()` should return a string that represents the title and the description of the page respectively. All other methods should output their content directly and their names describe what they should output pretty well.

```PHP
public final function addStylesheet(string $name, array $attributes = [], string $appendQuery = 'mtime')
public final function addScript(string $name, array $attributes = ['defer'], string $appendQuery = 'mtime')
```

These two methods allow for the addition of scripts and stylesheets that are output within the head element of the page, after the `initialization()` method and before the `pageHeader()` method. Therefore, they should be used inside the `initialization()` method and any [components](#components)' `initialize()` methods should also be called there in order to add necessary stylesheets and scripts.

# Localization
Now that you know how to create a basic page, perhaps, it is time to make it available in more than one language. Localization can be enabled in the `*/source/include/settings.php` file along with some other localization specific settings. If enabled then all requests to public pages should include the lang part in the URL:

```
http://example.com/[prefix]/lang/page_name/[args]
```

> Ajax and administration pages make an exception that they do NOT require the language argument in any case. In case that localization is enabled they are provided with the default language from the settings. This covers cases where ajax and/or administration localization is not needed but it is needed for the public.

The localized elements are then contained inside plain PHP files that return an array. These files are located in `*/source/include/locale/{lang}` where `{lang}` represents the same lang value as in the URL. For example locales for `http://example.com/en/shop` are located inside `en` folder. Locales can be freely named and should return an array:

__*/source/include/locale/en/common.php__

```PHP
return [
    'page_title' => 'This is my first page',
    'page_title' => 'My first app!',
];
```

Locales are then loaded using the `Localization::load()` method or the wrapping `Base::loadLocale()` method. Both of which accept the name of the locale file as an argument (in this case 'common'). Localization elements can then be accessed by calling `Localization::get()` or `Base::localize()`.

> Note that localization elements can be of any type and not necessarily strings. For more information check the documented methods in their respective classes.

# Databases
Good, now we can translate our pages, but what about databases? They can be a tedious business sometimes. Worry not, Selio has got your back! It extends the [PDO](http://php.net/manual/en/book.pdo.php) and [PDOStatement](http://php.net/manual/en/class.pdostatement.php) classes as `\selio\db\DatabaseConnection` and `\selio\db\DatabaseStatement` providing them with some steroid behaviour and appearance (or not so much) while they can still be used as you would use [PDO](http://php.net/manual/en/book.pdo.php) or [PDOStatement](http://php.net/manual/en/class.pdostatement.php).

Selio has a `*/source/include/db.php` file that contains information about database connections in the format first provided. This data is then used by the `\selio\db\Database` class to establish database connections and saves you from the effort to rewrite connections' data.

Connections can be manually create by using `Database::connect()` method or the wrapping method `Base::connectToDatabase()`. Additionally automatic database connections can be established in any class, extending `\selio\Base`, which is especially useful for page classes. This is done by providing a protected property with a value equal to the name of the connection data within the `db.php` file:

```PHP
class Front extends PublicPage {
    protected $connection = 'default_connection';
}
```

Selio then uses the name of this connection to retrieve its data and establish a connection to the database which is the stored in this very same property and can be retrieved at any point by calling `$this->connection`. This type of connections is typically used while manual connections are useful in cases where more than one database connection is required.

> The Database class stores any instance of a database connection and returns in on a duplicate attempt to establish a connection for the same connection name and data. Therefore, explicit connection clusing should be done by calling Database::closeConnection() method.

# Components
Components! Who doesn't like splitting large pages into smaller, reusable components...? There are two types of components: `\selio\page\PageComponent` and `\selio\page\AjaxComponent`. The former is used primarily and we will be talking about it in this section. The latter is described below in the [AJAX](#ajax) section.

Components are special in that they keep a reference to the first (main page) that has been instantiated and thus they can make use of its features at `$this->pageInstance`. They also mirror the automatic database connection that has been stored in the `PublicPage::$connection` property. This all means that the components can simply be used to describe parts of the page. The `\selio\Base` class provides a few useful methods related to components that let you instantiate them without having to type long and unnecessary use statements at the top:

```PHP
public final static function invokeComponent(string $component, ...$args) : Component {
    // Creates a new instance of a component
    // every time this method is called.
}

public final static function getComponent(string $component, ...$args) : Component {
    // Creates an instance of a component once and then
    // returns it in any following calls to this method.
}
```

These methods create instances of classes relative to the `\components` namespace. This means that a call to component `blog/post` will create an instance of a class at `\components\blog\post` namespace.

# Routing
For the routing we use the same components, described above, but instead of putting them inside a `\components` namespace and folder we put them inside the `\routes` namespace and folder. It is specified by providing a protected property to the page class. It can be used in page classes of any type. A simple example for our public Blog page would look like:

```PHP
class Blog extends PublicPage {
    protected $routes = [
        'post/*' => 'blog/post',
        'categories' => 'blog/categories',
        'default' => 'blog/main'
    ];
}
```

The keys are the URLs that will be matched. Only the args after the page name should be specified there. Key with value of `default` is the default route to be followed when no other is matched. Our examples then route as follows. From the example above the first route `post/*` routes and example URL `http://example.com/[prefix]/[lang]/page_name/post/*` to the component `\routes\blog\post`, where the star (*) represents any value. The second example should be pretty obvious... Now for the cases where you do not want to route but want to provide your default API methods with additional arguments from the URL you can do something like:

```PHP
protected $routes = [
    '*/*/*' => 'blog/main'
];
```
This will allow any path with three additional arguments after the page name to be visited. As a side note, any URL that does not match a route is discarded any arguments that come after the page name and is redirected to it. For example if the URL `http://example.com/en/blog/post/123/test` does not match our rule, it will be redirected to `http://example.com/en/blog`.

How are components used as page routes? Pretty simple. Every `\selio\page\PageComponent` has methods that can be extended to provide functionality when used as a route:

```PHP
abstract class PageComponent extends Component {
    public function initialize() {
        // Similarly to the page's one it is used to perform
        // initialization by adding scripts, stylesheets and
        // more... It is called after the page's
        // initialization() method.
    }
    public function build() {
        // It is used to provide the component's content and
        // is called INSTEAD of the page's pageContent() method.
    }
    public function finalize() {
        // Used for any explicit finalization, again it is
        // called after the page's finalization() method.
    }
}
```

# Caching
Sometimes there are database queries or data that takes a lot of processing to obtain and use and eats up a lot of resources on every request. This is where caching can help. The `\selio\Cache` class prrovides a few useful methods which are wrapped by `\selio\Base`. For more information you can check the documented methods in the class files.

The caching in its current form caches a string or array to a file and is therefore useful in cases where reading the file is faster or more reasonable than obtaining the result in the alternative way. The provided string or array is cached for a period of time after which it is recached. This means that every so often a single request or two will be slower than usual when they perform the necessary tasks to obtain the data and write it to a file and provde the cache to be used by the following requests until recaching is needed.

# AJAX
Any page class has a nice `Page::AJAX()` method which is called on ajax request. Ajax requests are defined by setting `X-Requested-With` header to `xmlhttprequest` (case insensitive).

The default ajax method is fine for fetching html from a page and dynamically updating it, but what if a more sophisticated request handling is needed. For example you need to support more than just GET requests and want to perform more actions. That is why the `\selio\page\AjaxPage` class exists:

```PHP
abstract class AjaxPage extends Page implements ArrayAccess {
    protected function onRequestStart() {
        // Called on ANY request method, before
        // the main request method is called.
    }

    // The following four methods are called, based
    // on the request method to the page.
    protected function onPOSTRequest() {}
    protected function onGETRequest() {}
    protected function onPUTRequest() {}
    protected function onDELETERequest() {}

    protected function onRequestEnd() {
        // Called on ANY request method, after
        // the main request method is called.
    }
}
```

As a convenience the AjaxPage class gathers request data from all possible sources ($_POST, $_GET, etc.) and stores it privately into the class. The request data can then be accessed and modified by simply using array access on the page object.

Ajax classes are located inside the `\ajax` namespace and folder. These pages can be requested by prepending __'ajax'__ before the page name in the URL:

```
http://example.com/[prefix]/[lang]/ajax/page_name/[args]
```

AjaxPages can also use [routes](#routing) in the same way as described above. The difference is that they should route to `\selio\page\AjaxComponent` classes, which have the same request methods as the ones shown above for the AjaxPage. These methods are called for the AjaxComponent that the route points to instead of calling them for the AjaxPage. These components can also use array access for the request data and access the same data that the main AjaxPage has access to; they mirror the database connection, etc.

# Authentication
Any single page class can be made to use authentication. Since the default authentication is a simple temporary out of the box solution it is recommended that you implement a better authentication method. For this you need to visit the settings and change the default authentication page and then create such a class. This class should extend `\selio\page\AuthenticationPage` and provide functionality to the public static AuthenticationPage::authenticate() method. This method should perform the authentication based on data provided to it in any way you find possible and suitable.

You then tell a page to use this authentication method by hard-coding a property in your page class:
```PHP
class BlogLoader extends AjaxPage {
    protected $auth = true;
}
```
Setting this property to true will force Selio to perform authentication before calling any other page methods. In case of authentication failure then the page, extending the AuthenticationPage class, specified in the settings will be rendered as html output in the same way the regular page would've been rendered.

> $auth property can be set for PublicPage, AjaxPage and AdminPage.

# Administration
Authentication allows for the creation of administration pages, based on your authentication method. The `\selio\page\AdminPage` class has authentication always enabled by default and should be used for the creation of administration pages. This does not mean that you cannot create your own custom class that extends `\selio\page\ContentPage` and has an always on authentication and use it instead.

Administration page classes should be located inside the `\pages\admin` namespace and folder. Administration pages are requested by prepending __'admin'__ before the page name in the URL:

```
http://example.com/[prefix]/[lang]/admin/page_name/[args]
```

These pages function no differently than the public pages described above. Their only privelige is that they have their own URL modification and always require authentication. They are meant to be used in order to create administration or profile management panels for your users.


# More...
There is a lot more contained within this tiny project like the Utility class that provides some frequently used or just very useful static methods, while the Core class has an array of static methods related to Selio's functionality and execution that can be used by you as well. Since the [documentation](https://github.com/muqg/Selio/wiki) won't be complete anytime soon (I believe), you will have to explore the documentation within the source files for more information.