<?php
// Copyright (C) 2009 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This links a specified or newly created GCAC issue to a specified
// encounter. It is invoked from pos_checkout.php via a jquery getScript().

require_once("../globals.php");
require_once("$srcdir/lists.inc");
require_once("$srcdir/acl.inc");

$issue   = 0 + (empty($_REQUEST['issue']) ? 0 : $_REQUEST['issue']);
$thispid = 0 + (empty($_REQUEST['thispid']) ? $pid : $_REQUEST['thispid']);
$thisenc = 0 + (empty($_REQUEST['thisenc']) ? 0 : $_REQUEST['thisenc']);

if (!acl_check('patients', 'med')) {
  echo "alert('" . xl('Not authorized') . ".');\n";
  exit();
}

if (!($thisenc && $thispid)) {
  echo "alert('" . xl('Internal error: pid or encounter is missing.') . ".');\n";
  exit();
}

$msg = xl('Internal error!');

if ($issue) {
  $msg = xl('Issue') . " $issue " . xl('has been linked to visit') .
    " $thispid.$thisenc.";
}
else {
  $issue = sqlInsert("INSERT INTO lists ( " .
    "date, pid, type, title, activity, comments, begdate, user, groupname " .
    ") VALUES ( " .
    "NOW(), "                               .
    "'$thispid', "                          .
    "'ippf_gcac', "                         .
    "'" . xl('Auto-generated')      . "', " .
    "1, "                                   .
    "'', "                                  .
    "'" . date('Y-m-d')             . "', " .
    "'" . $_SESSION['authUser']     . "', " .
    "'" . $_SESSION['authProvider'] . "' "  .
   ")");

  if ($issue) {
    sqlStatement("INSERT INTO lists_ippf_gcac ( id ) VALUES ( $issue )");
    $msg = xl('An incomplete GCAC issue has been created and linked. Someone will need to complete it later.');
  }
}

if ($issue) {
  $query = "INSERT INTO issue_encounter ( " .
    "pid, list_id, encounter " .
    ") VALUES ( " .
    "'$thispid', '$issue', '$thisenc'" .
  ")";
  sqlStatement($query);
}

echo "alert('$msg');\n";
?>
