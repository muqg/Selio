<?php

const SELIO_SETTINGS = [
    // Website's name/title.
    'website' => 'Selio App',

    // Enables multi-language support for the website URLs.
    # Default: true
    'selio.multiLanguage' => true,

    // The default language to use when multi-language is enabled.
    // This is normally only used as a fallback on error.
    # Default: 'en'
    'selio.defaultLanguage' => 'en',

    // Enable automatic language detection.
    # Default: true
    'selio.autoDetermineLanguage' => true,

    // Supported languages.
    # Default: ['en']
    'selio.languages' => ['en'],

    // Selio's error log file path.
    # Default: 'selio_log'
    'selio.logFile' => 'selio_log',

    // Flags that will be ignored and not logged by Core::logFlagged() method.
    // It is either an array of the ignored flag names or a string 'all' to ignore all.
    # Default: []
    'selio.logIgnoreFlags' => [],

    // If enabled it will record total execution time and log
    // if it exceeds a cretain time frame.
    # Default: true
    'selio.monitorExecutionTime' => true,

    // Indicates the maximum execution time in miliseconds
    // that will not cause a log entry when execution
    // time monitoring is enabled.
    # Default: 75
    'selio.executionTime' => 75,

    // Fully qualified namespaced name to the authentication page that will be
    // built when request fails to authenticate.
    # Default: 'pages\selio\Authentication'
    'selio.authenticationPage' => 'pages\selio\Authentication',

    // Charset for the website's meta charset tag.
    # Default: 'utf-8'
    'selio.page.charset' => 'utf-8',

    // Path to the website tab icon.
    # Default: 'icon.png'
    'selio.page.iconPath' => 'icon.png',

    // When enabled pages will look for and attempt to load
    // a script and stylesheet file of the same name as the
    // page and load them as a last script/stylesheet element.
    # Default: true
    'selio.page.loadDefaultAssets' => true,



    // ===========================
    // Add custom settings below:

];
