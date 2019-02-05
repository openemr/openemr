<?php
/**
 * Encounter form view script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");

$disabled = "disabled";

// If we are allowed to change encounter dates...
if (acl_check('encounters', 'date_a')) {
    $disabled = "";
}

$viewmode = true;
require_once("common.php");
