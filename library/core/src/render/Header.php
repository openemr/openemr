<?php
/**
 * OpenEMR <http://open-emr.org>.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class Header.
 *
 * Helper class to generate some `<script>` and `<link>` elements based on a
 * configuration file. This file would be a good place to include other helpers
 * for creating a `<head>` element, but for now it sufficently handles the
 * `includeAsset()`
 *
 * @package OpenEMR
 * @subpackage Core
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 */
class Header
{

    static public function setupHeader($assets)
    {
        echo self::includeAsset($assets);
    }

    /**
     * Include an asset from a config file.
     *
     * Static function to read in a YAML file into an array, check if the
     * $assets keys are in the config file, and from the config file generate
     * the HTML for a `<script>` or `<link>` tag.
     *
     * See root_dir/config/config.yaml
     *
     * Example:
     * ```php
     * // From a view file, inside of <head>
     * use OpenEMR\Core\Header;
     * Header::includeAsset([
     *     'datetimepicker',
     *     'jquery-ui',
     * ];
     * ```
     *
     * The above example will render 2 `<script>` tags and 1 `<link>` tag which
     * bring in the datetimepicker and jquery-ui versions defined in config.yaml
     *
     * @param array|string $assets Asset(s) to include
     * @throws ParseException If unable to parse the config file
     * @return string
     */
    static public function includeAsset($assets)
    {

        if (is_string($assets)) {
            $assets = [$assets];
        }

        // @TODO Hard coded the path to the config file, not good RD 2017-05-27
        $map = self::readConfigFile("{$GLOBALS['webroot']}/config/config.yaml");

        $scripts = [];
        $links = [];

        // First things first, load the theme
        $links[] = self::loadTheme();

        foreach ($map as $k => $opts) {
            $autoload = (isset($opts['autoload'])) ? $opts['autoload'] : false;
            $rtl = (isset($opts['rtl'])) ? $opts['rtl'] : false;
            if ($autoload === true || in_array($k, $assets)) {
                $tmp = self::buildAsset($opts);

                foreach ($tmp['scripts'] as $s) {
                    $scripts[] = $s;
                }

                foreach ($tmp['links'] as $l) {
                    $links[] = $l;
                }

                if ($rtl) {
                    $tmpRtl = self::buildAsset($rtl);
                    foreach ($tmpRtl['scripts'] as $s) {
                        $scripts[] = $s;
                    }

                    foreach ($tmpRtl['links'] as $l) {
                        $links[] = $l;
                    }
                }
            }
        }

        $linksStr = implode("", $links);
        $scriptsStr = implode("", $scripts);
        return "\n{$linksStr}\n{$scriptsStr}\n";
    }

    static private function buildAsset($opts)
    {
        $script = (isset($opts['script'])) ? $opts['script'] : false;
        $link = (isset($opts['link'])) ? $opts['link'] : false;
        $basePath = self::replaceBasePathVariables($opts['basePath']);

        $scripts = [];
        $links = [];

        if ($script) {
            $path = self::createFullPath($basePath, $script);
            $scripts[] = self::createElement($path, 'script');
        }

        if ($link) {
            $path = self::createFullPath($basePath, $link);
            $links[] = self::createElement($path, 'link');
        }

        return ['scripts' => $scripts, 'links' => $links];
    }

    static private function loadTheme()
    {
        return self::createElement($GLOBALS['css_header'], 'link');
    }

    static private function replaceBasePathVariables($basePath)
    {
        $re = '/%(.*)%/';
        $basePathMatches = [];
        preg_match_all($re, $basePath, $basePathMatches, PREG_SET_ORDER, 0);

        foreach ($basePathMatches as $match) {
            if (array_key_exists($match[1], $GLOBALS)) {
                $basePath = str_replace($match[0], $GLOBALS["{$match[1]}"], $basePath);
            }
        }
        return $basePath;
    }

    /**
     * Create the actual HTML element.
     *
     * @param string $path File path to load
     * @param string $type Must be `script` or `link`
     * @return string mixed HTML element
     */
    static private function createElement($path, $type)
    {

        $script = "<script type=\"text/javascript\" src=\"%path%\"></script>\n";
        $link = "<link rel=\"stylesheet\" href=\"%path%\" type=\"text/css\">\n";

        $template = ($type == 'script') ? $script : $link;
        $v = $GLOBALS['v_js_includes'];
        $path = $path . "?v={$v}";
        return str_replace("%path%", $path, $template);

    }

    static private function createFullPath($base, $path)
    {
        return $base . $path;
    }

    static private function readConfigFile($file)
    {
        try {
            $config = Yaml::parse(file_get_contents($file));
            return $config['assets'];
        } catch (ParseException $e) {
            error_log($e->getMessage());
            // @TODO need to handle this better. RD 2017-05-24
        }
    }

}
