<?php
// Copyright (C) 2009 Jason Morrill <jason@italktech.net>
// Rewritten by Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// This file is used to find patient(s) that match the given
// criteria passed in
//
// OUTPUT is varied and based on the returntype parameter
//
// Important - Ensure that display_errors=Off in php.ini settings.
//
require_once("../../interface/globals.php");
require_once("{$GLOBALS['srcdir']}/sql.inc");
require_once("{$GLOBALS['srcdir']}/formdata.inc.php");

function myGetValue($fldname) {
  $val = formData($fldname, 'G', true);
  if ($val == 'undefined') $val = '';
  return $val;
}

function myQueryPatients($where) {
  $sql = "SELECT count(*) AS count FROM patient_data WHERE $where";
  $row = sqlQuery($sql);
  return $row['count'];
}

$fname  = myGetValue('fname');
$mname  = myGetValue('mname');
$lname  = myGetValue('lname');
$pubpid = myGetValue('pubpid');
$ss     = myGetValue('ss');

$error = 0;
$message = '';

if ($pubpid) {
  if (myQueryPatients("pubpid LIKE '$pubpid'")) {
    $error = 2;
    $message = xl('A patient with this ID already exists.');
    $fname = $mname = $lname = $ss = '';
  }
}

if (!$error && $ss) {
  if (myQueryPatients("ss LIKE '$ss'")) {
    $error = 2;
    $message = xl('A patient with this SS already exists.');
    $fname = $mname = $lname = $pubpid = '';
  }
}

$nametest = "fname LIKE '$fname' AND lname LIKE '$lname'";
if ($mname != '') $nametest .= " AND mname LIKE '$mname'";

if (!$error && ($fname || $lname || $mname)) {
  if (myQueryPatients("$nametest")) {
    $error = 1;
    $message = xl('A patient with this name already exists.');
    $pubpid = $ss = '';
  }
}

if ($error) {
  if ($error == 1) {
    echo "force_submit = true;\n";
    echo "f.create.value = '" . xl('Force Create New Patient') . "';\n";
  }
  $message = addslashes($message);
  echo "show_matches('$fname', '$mname', '$lname', '$pubpid', '$ss', '$message')\n";
}
else {
  echo "f.submit()\n";
}

?>
