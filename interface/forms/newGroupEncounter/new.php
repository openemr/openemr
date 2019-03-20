<?php
/**
 * Encounter form new script.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../../globals.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");

// todo -include_once("$srcdir/groups.inc");


/*// todo Check permission to create encounters.
$tmp = getGroupData($pid, "squad");
if (($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) ||
     ! (acl_check('encounters', 'notes_a' ) ||
        acl_check('encounters', 'notes'   ) ||
        acl_check('encounters', 'coding_a') ||
        acl_check('encounters', 'coding'  ) ||
        acl_check('encounters', 'relaxed' )))
{
  echo "<body>\n<html>\n";
  echo "<p>(" . xlt('New encounters not authorized'). ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}*/

$viewmode = false;
if (acl_check("groups", "glog", false, 'write')) {
    require_once("common.php");
} else {
    echo xlt("access not allowed");
}
