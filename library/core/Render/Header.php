<?php
/**
 * OpenEMR (http://open-emr.org).
 */

namespace OpenEMR\Core;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class Header.
 *
 * @package OpenEMR
 * @subpackage Core
 */
class Header
{

    /**
     * Include an asset from a config file.
     *
     * Read in a YAML file into an array, check if the $assets keys are in the
     * config file, and from the config file generate the HTML for a `<script>`
     * or `<link>` tag. See <root_dir>/config/config.yaml
     *
     * @param array|string $assets Assets to include
     */
    static public function includeAsset($assets)
    {

        if (is_string($assets)) {
            $assets = [$assets];
        }

        try {
            $file = "{$GLOBALS['webroot']}/config/config.yaml";
            $config = Yaml::parse(file_get_contents($file));
            $map = $config['assets'];
        } catch (ParseException $e) {
            die('Error here');
        }

        $script = "<script type=\"text/javascript\" src=\"%src%\"></script>\n";
        $link = "<link rel=\"stylesheet\" href=\"%href%\" type=\"text/css\">\n";

        $scripts = [];
        $links = [];

        foreach ($assets as $asset) {
            if (array_key_exists($asset, $map)) {
                $re = '/%(.*)%/';
                $basePath = $map["{$asset}"]['basePath'];
                $basePathMatches = [];
                preg_match_all($re, $basePath, $basePathMatches, PREG_SET_ORDER, 0);

                foreach ($basePathMatches as $match) {
                    if (array_key_exists($match[1], $GLOBALS)) {
                        $basePath = str_replace($match[0], $GLOBALS["{$match[1]}"], $basePath);
                    }
                }

                if ($map["{$asset}"]['script']) {
                    $path = $basePath . $map["{$asset}"]['script'];
                    $scripts[] = str_replace("%src%", $path, $script);
                }

                if ($map["{$asset}"]['link']) {
                    $path = $basePath . $map["{$asset}"]['link'];
                    $links[] = str_replace("%href%", $path, $link);
                }
            }
        }

        echo implode("", $links);
        echo implode("", $scripts);

    }

}
