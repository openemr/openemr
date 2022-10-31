<?php

/**
 * OpenEMR <https://open-emr.org>.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Events\Core\ScriptFilterEvent;
use OpenEMR\Events\Core\StyleFilterEvent;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class Header.
 *
 * Helper class to generate some `<script>` and `<link>` elements based on a
 * configuration file. This file would be a good place to include other helpers
 * for creating a `<head>` element, but for now it sufficently handles the
 * `setupHeader()`
 *
 * @package OpenEMR
 * @subpackage Core
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017-2022 Robert Down
 */
class Header
{
    private static $scripts;
    private static $links;
    private static $isHeader;

    /**
     * Setup various <head> elements.
     *
     * See root_dir/config/config.yaml for available assets
     *
     * Example usage in a PHP view script:
     * ```php
     * // Top of script with require_once statements
     * use OpenEMR\Core\Header;
     *
     * // Inside of <head>
     * // If no special assets are needed:
     * Header::setupHeader();
     *
     * // If 1 special asset is needed:
     * Header::setupHeader('key-of-asset');
     *
     * // If 2 or more assets are needed:
     * Header::setupHeader(['array', 'of', 'keys']);
     *
     * // If wish to not include a normally autoloaded asset
     * Header::setupHeader('no_main-theme');
     * ```
     *
     * Inside of a twig template (Parameters same as before):
     * ```html
     * {{ includeAsset() }}
     * ```
     *
     * Inside of a smarty template, use | (pipe) delimited string of key names
     * ```php
     * {headerTemplate}
     * {headerTemplate assets='key-of-asset'}  (1 optional assets)
     * {headerTemplate assets='array|of|keys'}  (multiple optional assets. ie. via | delimiter)
     * ```
     *
     * The above example will render `<script>` tags and `<link>` tag which
     * bring in the requested assets from config.yaml
     *
     * @param array|string $assets Asset(s) to include
     * @param boolean $echoOutput - if true then echo
     *                              if false then return string
     * @throws ParseException If unable to parse the config file
     * @return string
     */
    public static function setupHeader($assets = [], $echoOutput = true)
    {
        // Required tag
        $output = "\n<meta charset=\"utf-8\" />\n";
        // Makes only compatible with MS Edge
        $output .= "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\" />\n";
        // BS4 required tag
        $output .= "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1, shrink-to-fit=no\" />\n";
        // Favicon
        $output .= "<link rel=\"shortcut icon\" href=\"" . $GLOBALS['images_static_relative'] . "/favicon.ico\" />\n";
        $output .= self::setupAssets($assets, true, false);

        // we need to grab the script
        $scriptName = $_SERVER['SCRIPT_NAME'];

        // we fire off events to grab any additional module scripts or css files that desire to adjust the currently executed script
        $scriptFilterEvent = new ScriptFilterEvent(basename($scriptName));
        $scriptFilterEvent->setContextArgument(ScriptFilterEvent::CONTEXT_ARGUMENT_SCRIPT_NAME, $scriptName);
        $apptScripts = $GLOBALS['kernel']->getEventDispatcher()->dispatch($scriptFilterEvent, ScriptFilterEvent::EVENT_NAME);

        $styleFilterEvent = new StyleFilterEvent($scriptName);
        $styleFilterEvent->setContextArgument(StyleFilterEvent::CONTEXT_ARGUMENT_SCRIPT_NAME, $scriptName);
        $apptStyles = $GLOBALS['kernel']->getEventDispatcher()->dispatch($styleFilterEvent, StyleFilterEvent::EVENT_NAME);
        // note these scripts have been filtered to be in the same origin as the current site in pnadmin.php & pnuserapi.php {

        if (!empty($apptScripts->getScripts())) {
            $output .= "<!-- Module Scripts Started -->";
            foreach ($apptScripts->getScripts() as $script) {
                // we want versions appended
                $output .= Header::createElement($script, 'script', false);
            }
            $output .= "<!-- Module Scripts Ended -->";
        }

        if (!empty($apptStyles->getStyles())) {
            $output .= "<!-- Module Styles Started -->";
            foreach ($apptStyles->getStyles() as $cssSrc) {
                // we want version appended
                $output .= Header::createElement($cssSrc, 'style', false);
            }
            $output .= "<!-- Module Styles Ended -->";
        }

        if ($echoOutput) {
            echo $output;
        } else {
            return $output;
        }
    }

    /**
     * Can call this function directly rather than using above setupHeader function
     *  if do not want to include the autoloaded assets.
     *
     * @param array $assets Asset(s) to include
     * @param boolean $headerMode - if true, then include autoloaded assets
     *                              if false, then do not include autoloaded assets
     * @param boolean $echoOutput - if true then echo
     *                              if false then return string
     */
    public static function setupAssets($assets = [], $headerMode = false, $echoOutput = true)
    {
        if ($headerMode) {
            self::$isHeader = true;
        } else {
            self::$isHeader = false;
        }

        try {
            if ($echoOutput) {
                echo self::includeAsset($assets);
            } else {
                return self::includeAsset($assets);
            }
        } catch (\InvalidArgumentException $e) {
            error_log(errorLogEscape($e->getMessage()));
        }
    }

    /**
     * Include an asset from a config file.
     *
     * Static function to read in a YAML file into an array, check if the
     * $assets keys are in the config file, and from the config file generate
     * the HTML for a `<script>` or `<link>` tag.
     *
     * This is a private function, use Header::setupHeader() instead
     *
     * @param array|string $assets Asset(s) to include
     * @throws ParseException If unable to parse the config file
     * @return string
     */
    private static function includeAsset($assets = [])
    {

        if (is_string($assets)) {
            $assets = [$assets];
        }

        // @TODO Hard coded the path to the config file, not good RD 2017-05-27
        $map = self::readConfigFile("{$GLOBALS['fileroot']}/config/config.yaml");
        self::$scripts = [];
        self::$links = [];

        self::parseConfigFile($map, $assets);

        /* adding custom assets in addition */
        if (is_file("{$GLOBALS['fileroot']}/custom/assets/custom.yaml")) {
            $customMap = self::readConfigFile("{$GLOBALS['fileroot']}/custom/assets/custom.yaml");
            self::parseConfigFile($customMap, $assets);
        }

        $linksStr = implode("", self::$links);
        $scriptsStr = implode("", self::$scripts);
        return "\n{$linksStr}\n{$scriptsStr}\n";
    }

    /**
     * Parse assets from config file
     *
     * @param array $map Assets to parse into self::$scripts and self::$links
     * @param array $selectedAssets
     * @return void
     */
    private static function parseConfigFile($map, $selectedAssets = array())
    {
        $foundAssets = [];
        $excludedCount = 0;
        foreach ($map as $k => $opts) {
            $autoload = (isset($opts['autoload'])) ? $opts['autoload'] : false;
            $allowNoLoad = (isset($opts['allowNoLoad'])) ? $opts['allowNoLoad'] : false;
            $alreadyBuilt = (isset($opts['alreadyBuilt'])) ? $opts['alreadyBuilt'] : false;
            $loadInFile = (isset($opts['loadInFile'])) ? $opts['loadInFile'] : false;
            $rtl = (isset($opts['rtl'])) ? $opts['rtl'] : false;

            if ((self::$isHeader === true && $autoload === true) || in_array($k, $selectedAssets) || ($loadInFile && $loadInFile === self::getCurrentFile())) {
                if ($allowNoLoad === true) {
                    if (in_array("no_" . $k, $selectedAssets)) {
                        $excludedCount++;
                        continue;
                    }
                }
                $foundAssets[] = $k;

                $tmp = self::buildAsset($opts, $alreadyBuilt);

                foreach ($tmp['scripts'] as $s) {
                    self::$scripts[] = $s;
                }

                if (($k == "bootstrap") && ((!in_array("no_main-theme", $selectedAssets)) || (in_array("patientportal-style", $selectedAssets)))) {
                    // Above comparison is to skip bootstrap theme loading when using a main theme or using the patient portal theme
                    //  since bootstrap theme is already including in main themes and portal theme via SASS.
                } else if ($k == "compact-theme" && (in_array("no_main-theme", $selectedAssets) || empty($GLOBALS['enable_compact_mode']))) {
                  // Do not display compact theme if it is turned off
                } else {
                    foreach ($tmp['links'] as $l) {
                        self::$links[] = $l;
                    }
                }

                if ($rtl && !empty($_SESSION['language_direction']) && $_SESSION['language_direction'] == 'rtl') {
                    $tmpRtl = self::buildAsset($rtl, $alreadyBuilt);
                    foreach ($tmpRtl['scripts'] as $s) {
                        self::$scripts[] = $s;
                    }

                    if ($k == "compact-theme" && (in_array("no_main-theme", $selectedAssets) || !$GLOBALS['enable_compact_mode'])) {
                    } else {
                        foreach ($tmpRtl['links'] as $l) {
                            self::$links[] = $l;
                        }
                    }
                }
            }
        }

        if (($thisCnt = count(array_diff($selectedAssets, $foundAssets))) > 0) {
            if ($thisCnt !== $excludedCount) {
                (new SystemLogger())->error("Not all selected assets were included in header", ['selectedAssets' => $selectedAssets, 'foundAssets' => $foundAssets]);
            }}
    }

    /**
     * Build an html element from config options.
     *
     * @var array $opts Options
     * @var boolean $alreadyBuilt - This means the path with cache busting segment has already been built
     * @return array Array with `scripts` and `links` keys which contain arrays of elements
     */
    private static function buildAsset($opts = array(), $alreadyBuilt = false)
    {
        $script = (isset($opts['script'])) ? $opts['script'] : false;
        $link = (isset($opts['link'])) ? $opts['link'] : false;
        $path = (isset($opts['basePath'])) ? $opts['basePath'] : '';
        $basePath = self::parsePlaceholders($path);

        $scripts = [];
        $links = [];

        if ($script) {
            if (!is_string($script) && !is_array($script)) {
                throw new \InvalidArgumentException("Script must be of type string or array");
            }

            if (is_string($script)) {
                $script = [$script];
            }

            foreach ($script as $k) {
                $k = self::parsePlaceholders($k);
                if ($alreadyBuilt) {
                    $path = $k;
                } else {
                    $path = self::createFullPath($basePath, $k);
                }
                $scripts[] = self::createElement($path, 'script', $alreadyBuilt);
            }
        }

        if ($link) {
            if (!is_string($link) && !is_array($link)) {
                throw new \InvalidArgumentException("Link must be of type string or array");
            }

            if (is_string($link)) {
                $link = [$link];
            }

            foreach ($link as $l) {
                $l = self::parsePlaceholders($l);
                if ($alreadyBuilt) {
                    $path = $l;
                } else {
                    $path = self::createFullPath($basePath, $l);
                }
                $links[] = self::createElement($path, 'link', $alreadyBuilt);
            }
        }

        return ['scripts' => $scripts, 'links' => $links];
    }

    /**
     * Parse a string for $GLOBAL key placeholders %key-name%.
     *
     * Perform a regex match all in the given subject for anything wrapped in
     * percent signs `%some-key%` and if that string exists in the $GLOBALS
     * array, will replace the occurence with the value of that key.
     *
     * @param string $subject String containing placeholders (%key-name%)
     * @return string The new string with properly replaced keys
     */
    public static function parsePlaceholders($subject)
    {
        $re = '/%(.*)%/';
        $matches = [];
        preg_match_all($re, $subject, $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
            if (array_key_exists($match[1], $GLOBALS)) {
                $subject = str_replace($match[0], $GLOBALS["{$match[1]}"], $subject);
            }
        }

        return $subject;
    }

    /**
     * Create the actual HTML element.
     *
     * @param string $path File path to load
     * @param string $type Must be `script` or `link`
     * @return string mixed HTML element
     */
    private static function createElement($path, $type, $alreadyBuilt)
    {

        $script = "<script src=\"%path%\"></script>\n";
        $link = "<link rel=\"stylesheet\" href=\"%path%\" />\n";

        $template = ($type == 'script') ? $script : $link;
        if (!$alreadyBuilt) {
            $v = $GLOBALS['v_js_includes'];
            // need to handle header elements that may already have a ? in the parameter.
            if (strrpos($path, "?") !== false) {
                $path = $path . "&v={$v}";
            } else {
                $path = $path . "?v={$v}";
            }
        }
        return str_replace("%path%", $path, $template);
    }

    /**
     * Create a full path from given parts.
     *
     * @param string $base Base path
     * @param string $path specific path / filename
     * @return string The full path
     */
    private static function createFullPath($base, $path)
    {
        return $base . $path;
    }

    /**
     * Read a config file and turn it into an array.
     *
     * @param string $file Full path to filename
     * @return array Array of assets
     */
    private static function readConfigFile($file)
    {
        try {
            $config = Yaml::parse(file_get_contents($file));
            return $config['assets'];
        } catch (ParseException $e) {
            error_log(errorLogEscape($e->getMessage()));
            // @TODO need to handle this better. RD 2017-05-24
        }
    }

    /**
     * Return relative path to current file
     *
     * @return string The  current file
     */
    private static function getCurrentFile()
    {
        //remove web root and query string
        return str_replace($GLOBALS['webroot'] . '/', '', strtok($_SERVER["REQUEST_URI"], '?'));
    }
}
