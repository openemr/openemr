<?php
/**
 * sets pid
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
*/

require_once("../../interface/globals.php");
require_once("$srcdir/pid.inc");

if ($_GET["set_pid"] && $_GET["set_pid"] != $_SESSION["pid"]) {
    setpid($_GET["set_pid"]);
}
