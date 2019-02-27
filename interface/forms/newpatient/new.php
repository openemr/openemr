<?php
/**
 * Encounter form new script.
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

// Check permission to create encounters.
$tmp = getPatientData($pid, "squad");
if (($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) ||
  !acl_check_form('newpatient', '', array('write', 'addonly'))) {
    echo "<body>\n<html>\n";
    echo "<p>(" . xlt('New encounters not authorized') . ")</p>\n";
    echo "</body>\n</html>\n";
    exit();
}

$viewmode = false;
require_once("common.php");
