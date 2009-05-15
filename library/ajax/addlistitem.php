<?php
/*
// Copyright (C) 2009 Jason Morrill <jason@italktech.net>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// 
// This file is used to add an item to the list_options table
//
// OUTPUT 
//   on error = NULL
//   on succcess = JSON data, array of "value":"title" for new list of options
*/

include_once("../../interface/globals.php");
include_once("{$GLOBALS['srcdir']}/sql.inc");

// check for required values
if ($_GET['listid'] == "" || $_GET['newitem'] == "") exit;

// set the values for the new list item
$is_default = 0;
$list_id = $_GET['listid'];
$title = trim($_GET['newitem']);
$option_id = preg_replace("/\W/", "_", $title);
$option_value = 0;

// make sure we're not adding a duplicate entry
$exists = sqlQuery("SELECT * FROM list_options WHERE ".
                    " list_id='".$list_id."'".
                    " and option_id='".trim($option_id)."'" .
                    " and title='".trim($title). "'" 
                    );
if ($exists) { exit; }

// determine the sequential order of the new item,
// it should be the maximum number for the specified list plus one
$seq = 0;  
$row = sqlQuery("SELECT max(seq) as maxseq FROM list_options WHERE list_id= '".$list_id."'");
$seq = $row['maxseq']+1;

// add the new list item
$rc = sqlInsert("INSERT INTO list_options ( " .
                "list_id, option_id, title, seq, is_default, option_value " .
                ") VALUES (" .
                "'".$list_id."'".
                ",'".trim($option_id)."'" .
                ",'".trim($title). "'" .
                ",'".$seq."'" .
                ",'".$is_default."'" .
                ",'".$option_value."'".
                ")"
);

// return JSON data of list items on success
echo '{ "options": [';
$comma = "";
$lres = sqlStatement("SELECT * FROM list_options WHERE list_id = '$list_id' ORDER BY seq");
while ($lrow = sqlFetchArray($lres)) {
    echo $comma;
    echo '{"id":"'.$lrow['option_id'].'",';
    echo '"title":"'.$lrow['title'].'"}';
    $comma = ",";
}
echo "]}";
exit;

?>
