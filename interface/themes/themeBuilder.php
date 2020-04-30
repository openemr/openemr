<?php

/**
 * Build custom CSS elements defined in the Admin pages.
 *
 * @package OpenEMR
 * @subpackage Theme
 * @author Robert Down <robertdown@live.com>
 * @author Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2017 Robert Down
 * @copyright Copyright (c) 2020 Tyler Wrenn
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// need to skip auth to allow use of this script from the login script
// this script is safe to ignore auth (takes no user input)
$ignoreAuth = true;

require_once __DIR__ . '/../../interface/globals.php';

header('Content-Type: text/css');
//set headers to NOT cache a page
header("Cache-Control: no-cache, must-revalidate"); //HTTP 1.1
header("Pragma: no-cache"); //HTTP 1.0
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>

@import "<?php echo $GLOBALS['fonts_dir']; ?>/lato/lato.css";
body {
    font-family: <?php echo $GLOBALS['font-family']; ?> !important;
    font-size: <?php echo $GLOBALS['font-size']; ?> !important;
}
