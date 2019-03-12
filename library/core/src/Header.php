<?php
/**
 * OpenEMR <http://open-emr.org>.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Core;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class clsCfgAssets
{
    private $assets = [];
    private $autoIncl = [];
    private $reviewed = FALSE;
    // Inject localizations
    private $zSrc = '';
    private $zFn = '';
    // Dev Mode
    private $devAssist = TRUE;

    function __construct($vCfg = '')
    {
        if (empty($vCfg)) {
            $vCfg = "{$GLOBALS['fileroot']}/config/config.yaml";
        }
        if (is_array($vCfg)) {
            foreach ($vCfg as $strCfg) {
                $this->addConfig($strCfg);
            }
        } else {
            $this->addConfig($vCfg);
        }
        // mdsupport - inject Localization for individual scripts
        $trace = debug_backtrace();
        $calledBy = pathinfo(end($trace)['file']);
        $calledBy = sprintf('%s_%s',
            substr($calledBy['dirname'], strlen($GLOBALS['webserver_root'])+1),
            str_replace('.', '_', $calledBy['filename'])
            );
        $this->zSrc = '/custom/zhdr.'.preg_replace('/[\\/\\\\]/', '.', $calledBy);
        $this->zFn = 'auto_'.preg_replace('/[\\/\\\\]/', '_', $calledBy);
    }

    public function addConfig($strCfg)
    {
        $this->assets = array_merge($this->assets, $this->getConfig($strCfg));
        $this->reviewed = FALSE;
    }

    private function getConfig($strCfg)
    {
        try {
            // Find unique globals
            $config = file_get_contents($strCfg);
            $pattern = '/%(.*)%/';
            $matches = [];
            preg_match_all($pattern, $config, $matches);
            $matches = array_unique($matches[1]);

            // Replace by actual settings
            foreach ($matches as $match) {
                if (array_key_exists($match, $GLOBALS)) {
                    $config = str_replace("%$match%", $GLOBALS[$match], $config);
                }
            }
            $config = Yaml::parse($config);

            // Validate, Transform each asset
            foreach ($config['assets'] as $pkg => $assetConfigEntry) {
                $config['assets'][$pkg] = $this->mapConfigEntry($assetConfigEntry);
            }
            return $config['assets'];
        } catch (ParseException $e) {
            error_log($e->getMessage());
        }
    }

    private function mapConfigEntry($assetConfigEntry)
    {
        // Allow rtl sessions to override or add settings
        if ((!empty($_SESSION['language_direction'])) && ($_SESSION['language_direction'] == 'rtl') && (!empty($assetConfigEntry['rtl']))) {
            $rtl = $assetConfigEntry['rtl'];
            unset($assetConfigEntry['rtl']);
            $assetConfigEntry = array_merge($assetConfigEntry, $rtl);
        }

        $cache_sfx = '?v='.$GLOBALS['v_js_includes'];
        foreach (['script', 'link'] as $tag) {
            if (!empty($assetConfigEntry[$tag])) {
                if (is_string($assetConfigEntry[$tag])) {
                    $assetConfigEntry[$tag] = [$assetConfigEntry[$tag]];
                }
                foreach ($assetConfigEntry[$tag] as $ix => $basename) {
                    if ((empty($assetConfigEntry['alreadyBuilt'])) || (!$assetConfigEntry['alreadyBuilt'])) {
                        $assetConfigEntry[$tag][$ix] = $assetConfigEntry['basePath'].$basename;
                        $assetConfigEntry['cache_sfx'] = $cache_sfx;
                    } else {
                        $assetConfigEntry['cache_sfx'] = '';
                    }
                }
            }
        }
        return $assetConfigEntry;
    }

    // Since scripts are permitted to add entries, must call this method before any output
    // For now, limited to creating list of autoload packages
    private function reviewAssetEntries() {
        // mdsupport - Append zSrc entries
        foreach (['link' => '.css', 'script' => '.js'] as $tag => $ext) {
            if (file_exists($GLOBALS['webserver_root'].$this->zSrc.$ext)) {
                $this->assets['zsrc']['autoload'] = TRUE;
                $this->assets['zsrc'][$tag] = $GLOBALS['webroot'].$this->zSrc.$ext;
            }
        }
        $assets = $this->assets;
        foreach ($assets as $pkg => $assetConfigEntry) {
            if (!empty($assetConfigEntry['autoload']) && $assetConfigEntry['autoload']) {
                $this->autoIncl[$pkg] = TRUE;
            }
        }
        $this->reviewed = TRUE;
    }

    // TBD : Implement asset dependencies here
    private function selectAssets($reqAssets, $exclAssets, $inclAuto) {
        if (!$this->reviewed) $this->reviewAssetEntries();
        $assets = $this->assets;
        if (!is_array($reqAssets)) {
            $reqAssets = [$reqAssets];
        }
        if ($inclAuto) {
            $reqAssets = array_keys(array_merge($this->autoIncl, array_flip($reqAssets)));
        }
        $assets = array_intersect_key($assets, array_flip($reqAssets));
        $assets = array_diff_key($assets, array_flip($exclAssets));
        return $assets;
    }

    public function getLinkTags($reqAssets = [], $exclAssets = [], $inclAuto = TRUE)
    {
        $strHtm = '';
        if ($this->devAssist) {
            $strHtm .= sprintf('<!-- %s : %s.css -->%s', xlt('Local'), $this->zSrc, PHP_EOL);
        }
        $assets = $this->selectAssets($reqAssets, $exclAssets, $inclAuto);
        foreach($assets as $asset) {
            if (!empty($asset['link'])) {
                foreach ($asset['link'] as $cssfile) {
                    $strHtm .= sprintf('<link rel="stylesheet" href="%s%s">%s',
                        $cssfile, $asset['cache_sfx'], PHP_EOL
                        );
                }
            }
        }
        return $strHtm;
    }

    public function getScriptTags($reqAssets = [], $exclAssets = [], $inclAuto = TRUE)
    {
        $strHtm = '';
        if ($this->devAssist) {
            $strHtm .= sprintf('<!-- %s : %s.js / %s -->%s', xlt('Local'), $this->zSrc, $this->zFn, PHP_EOL);
        }
        $assets = $this->selectAssets($reqAssets, $exclAssets, $inclAuto);
        foreach($assets as $asset) {
            if (!empty($asset['script'])) {
                foreach ($asset['script'] as $jsfile) {
                    $strHtm .= sprintf('<script type="text/javascript" src="%s?v=%s"></script>%s',
                        $jsfile, $asset['cache_sfx'], PHP_EOL
                        );
                }
            }
        }

        // mdsupport - Inject local coode after the standard
        $autofn = $this->zFn;
        $strHtm .= sprintf('
<script>
    $(document).ready(function() {
        if (typeof top.%s === "function") {
            top.%s($);
        } else if (typeof window.parent.%s === "function") {
            window.parent.%s($);
        }
    });
</script>',
            $autofn, $autofn, $autofn, $autofn);

        return $strHtm;
    }
}

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
 * @copyright Copyright (c) 2017 Robert Down
 */
class Header
{

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
     * @throws ParseException If unable to parse the config file
     * @return string
     */
    public static function setupHeader($assets = [])
    {
        try {
            html_header_show();
            echo self::includeAsset($assets);
        } catch (\InvalidArgumentException $e) {
            error_log($e->getMessage());
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

        // Map old 'no_' style to  exclude
        $exclAssets = [];
        foreach ($assets as $ix => $strAsset) {
            if (substr($strAsset, 0, 3) == 'no_') {
                $exclAssets[] = substr($strAsset, 3);
                unset($assets[$ix]);
            }
        }

        // @TODO Hard coded the path to the config file, not good RD 2017-05-27
        // New asset config file processing
        $objCfgAssets = new clsCfgAssets();
        if (file_exists($GLOBALS['fileroot'].'/custom/assets/custom.yaml')) {
            $objCfgAssets->addConfig($GLOBALS['fileroot'].'/custom/assets/custom.yaml');
        }

        // Maintaining old style output
        $strHtm = $objCfgAssets->getLinkTags($assets, $exclAssets)."\n";
        $strHtm .= $objCfgAssets->getScriptTags($assets, $exclAssets)."\n";

        return $strHtm;
    }
}
