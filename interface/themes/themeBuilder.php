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

/*
*   TODO: Fix when done with Bootstrap 4 update
*/

// need to skip auth to allow use of this script from the login script
// this script is safe to ignore auth (takes no user input)
$ignoreAuth=true;

require_once __DIR__ . '/../../interface/globals.php';

$cssFile = file_get_contents('themeBuilder.css');

// Allowed CSS replacements
$variables = [
    'font-family',
    'font-size',
];

// Allowed other replacements
$otherVariables = [
    'fonts_dir'
];

// Build string-replacement array
$re = "/%(.*)%/";
$matches = [];
preg_match_all($re, $cssFile, $matches, PREG_SET_ORDER);

foreach ($matches as $match) {
    $rawString = $match[0];
    $key = $match[1];
    if (!array_key_exists($key, $GLOBALS)) {
        $msg = sprintf("OpenEMR ERROR: The key '%s' was not found in the list of global variables in themeBuilder", $key);
        error_log(errorLogEscape($msg));
        $cssFile = str_replace($rawString, '', $cssFile);
        continue;
    }

    if ($GLOBALS["{$key}"] === '__default__') {
        $cssFile = str_replace($rawString, '', $cssFile);
        continue;
    }

    if (in_array($key, $variables)) {
        // This is a CSS variable, not a $GLOBAL value replacement
        $globalVal = $GLOBALS["{$key}"];
        // Ensure only contains valid characters
        if (preg_match('/[^A-Za-z0-9,"\-\s]/', $globalVal)) {
            $msg = sprintf("OpenEMR ERROR: The value '%s' contains illegal character in themeBuilder", $globalVal);
            error_log(errorLogEscape($msg));
            $cssFile = str_replace($rawString, '', $cssFile);
            continue;
        }
        $attribString = "{$key}: {$globalVal} !important;";
        $cssFile = str_replace($rawString, $attribString, $cssFile);
    } else if (in_array($key, $otherVariables)) {
        // $GLOBAL value replacement
        $cssFile = str_replace($rawString, $GLOBALS["{$key}"], $cssFile);
    } else {
        $msg = sprintf("OpenEMR ERROR: The key '%s' was not allowed in themeBuilder", $key);
        error_log(errorLogEscape($msg));
        $cssFile = str_replace($rawString, $attribString, $cssFile);
        continue;
    }
}

header('Content-Type: text/css');
//set headers to NOT cache a page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
echo $cssFile;
