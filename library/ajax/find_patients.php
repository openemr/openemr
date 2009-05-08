<?php
// Copyright (C) 2009 Jason Morrill <jason@italktech.net>
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
include_once("../../interface/globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");

// set an upper limit on the number of results returned from the database
$limit = $_GET['limit'];
$limit = 50;

if ($_GET['returntype'] == 'count') {
    // only get the number of patients matching the input criteria

    // input check - don't search if all input parameters are empty
    if ($_GET['fname'] == "" && $_GET['mname'] == "" && $_GET['lname'] == "" &&
        $_GET['pubpid'] == "" && $_GET['DOB'] == "" && $_GET['sex'] == "" &&
        $_GET['ss'] == "")
    { echo "0"; exit; }

    $sql = "select count(*) as total from patient_data where ";
    $sql_and = "";
    if ($_GET['fname']) { $sql .= $sql_and. " fname='".$_GET['fname']."'"; $sql_and = " AND "; }
    if ($_GET['mname']) { $sql .= $sql_and. " mname='".$_GET['mname']."'"; $sql_and = " AND "; }
    if ($_GET['lname']) { $sql .= $sql_and. " lname='".$_GET['lname']."'"; $sql_and = " AND "; }
    if ($_GET['pubpid']) { $sql .= $sql_and. " pubpid='".$_GET['pubpid']."'"; $sql_and = " AND "; }
    if ($_GET['DOB']) { $sql .= $sql_and. " DOB='".$_GET['DOB']."'"; $sql_and = " AND "; }
    if ($_GET['sex']) { $sql .= $sql_and. " sex='".$_GET['sex']."'"; $sql_and = " AND "; }
    if ($_GET['ss']) { $sql .= $sql_and. " ss='".$_GET['ss']."'"; $sql_and = " AND "; }
    $sql .= " limit ".$limit;

    $results = sqlStatement($sql);
    $row = sqlFetchArray($results);
    echo $row['total'];
    exit;
}

exit;

?>
