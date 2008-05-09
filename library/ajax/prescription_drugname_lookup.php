<?php
// Copyright (C) 2008 Jason Morrill <jason@italktech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// This file use is used specifically to look up drug names when
// writing a prescription. See the file:
//    templates/prescriptions/general_edit.html
// for additional information
//
// Important - Ensure that display_errors=Off in php.ini settings.
//
include_once("../../interface/globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");

$q = strtolower($_GET["q"]);
if (!$q) return;
$limit = $_GET['limit'];

$sql = "select drug_id, name from drugs where ".
            " name like ('".$q."%')".
            " order by name ".
            " limit ".$limit;
$rez = sqlStatement($sql);

while ($row = sqlFetchArray($rez)) {
    echo $row['name']."|".$row['drug_id']."\n";
}

?>
