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

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/lists.inc");

use OpenEMR\Common\Acl\AclMain;

// todo -include_once("$srcdir/groups.inc");


/*// todo Check permission to create encounters.
$tmp = getGroupData($pid, "squad");
if (($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) ||
     ! (AclMain::aclCheckCore('encounters', 'notes_a' ) ||
        AclMain::aclCheckCore('encounters', 'notes'   ) ||
        AclMain::aclCheckCore('encounters', 'coding_a') ||
        AclMain::aclCheckCore('encounters', 'coding'  ) ||
        AclMain::aclCheckCore('encounters', 'relaxed' )))
{
  echo "<body>\n<html>\n";
  echo "<p>(" . xlt('New encounters not authorized'). ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}*/

$viewmode = false;
if (AclMain::aclCheckCore("groups", "glog", false, 'write')) {
    require_once("common.php");
} else {
    echo xlt("access not allowed");
}
