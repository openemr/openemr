<?php
include_once("../../globals.php");
include_once("$srcdir/acl.inc");
include_once("$srcdir/lists.inc");

// Check permission to create encounters.
$tmp = getPatientData($pid, "squad");
if (($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) ||
     ! (acl_check('encounters', 'notes_a' ) ||
        acl_check('encounters', 'notes'   ) ||
        acl_check('encounters', 'coding_a') ||
        acl_check('encounters', 'coding'  ) ||
        acl_check('encounters', 'relaxed' )))
{
  echo "<body>\n<html>\n";
  echo "<p>(" . xl('New encounters not authorized'). ")</p>\n";
  echo "</body>\n</html>\n";
  exit();
}

$viewmode = false;
require_once("common.php");
?>
