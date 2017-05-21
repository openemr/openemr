<?php
/**
 * Build custom CSS elements defined in the Admin pages.
 *
 * @package OpenEMR
 * @subpackage Theme
 * @author Robert Down <robertdown@live.com>
 * @copyright Copyright (c) 2017 Robert Down
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once __DIR__ . '/../globals.php';

$fontFamily = $GLOBALS['font_family'];

$cssFile = file_get_contents('themeBuilder.css');

$variables = [
    'font-family',
    'font-size',
];

foreach ($variables as $v) {
    $globalVariable = $GLOBALS["{$v}"];
    if ($globalVariable === '__default__') {
        $cssFile = str_replace("%{$v}%", '', $cssFile);
    } else {
        $attributString = "{$v}: {$globalVariable} !important;";
        $cssFile = str_replace("%{$v}%", $attributString, $cssFile);
    }
}

header('Content-Type: text/css');
echo $cssFile;
