<?php
namespace selio;


/**
 * Provides basic localization methods.
 */
final class Localization {
    /**
     * Holds the locales' elements.
     * @var array
     */
    private static $elements = [];

    /**
     * Loads a locale file.
     * @param string $name The name of the locale file.
     */
    public static function load(string $name) {
        if(setting('selio.multiLanguage')) {
            $file = Core::getIncludePath('locale/' . SELIO_LANGUAGE . "/$name.php");

            if(file_exists($file)) {
                $elements = require_once $file;

                if(is_array($elements))
                    self::$elements = array_merge(self::$elements, $elements);
            }
            else {
                throw new LocalizationException(
                    "Unable to load locale '$name' for language " . SELIO_LANGUAGE
                );
            }
        }
    }

    /**
     * Returns a localized element. Accepts a variable number of arguments which
     * are used to format a string element.
     * @param string $key The key for the localized element. A nested key may be
     * provided where each array level is separated with a slash (/).
     * @param mixed ...$args Variable number of arguments to replace the format
     * placeholders with in the provided order. Can pass a single array of
     * key/value pairs to replace named format placeholders instead.
     */
    public static function get(string $key, ...$args) {
        $result = Utility::getArrayElement($key, self::$elements);

        if($result === null) {
            Core::logFlagged("Invalid key '$key'.", 'Localization');
        }
        else if($args) {
            if(!is_string($result)) {
                Core::logFlagged(
                    "Can only format a localization element of type string, {$type} given.",
                    'Localization'
                );
                return $result;
            }

            $namedValues = $args[0];
            if($namedValues && is_array($namedValues)) {
                foreach($namedValues as $name => $value) {
                    $searchValues[] = "{{$name}}";
                    $replaceValues[] = $value;
                }

            }
            else {
                $replaceValues = array_values($args);
                $count = count($args);
                for($i = 0; $i < $count; $i++)
                    $searchValues[] = "{{$i}}";
            }
            $result = str_replace($searchValues, $replaceValues, $result);
        }

        return $result;
    }

    /**
     * Gets the locale elements as a JSON.
     */
    public static function getJSON() : string {
        return json_encode(self::$elements, JSON_UNESCAPED_UNICODE);
    }
}