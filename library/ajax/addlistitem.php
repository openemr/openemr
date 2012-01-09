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
if ($_GET['listid'] == "" || trim($_GET['newitem']) == "" || trim($_GET['newitem_abbr']) == "") exit;

// set the values for the new list item
$is_default = 0;
$list_id = $_GET['listid'];
$title = trim($_GET['newitem']);
$option_id = trim($_GET['newitem_abbr']);
$option_value = 0;

// make sure we're not adding a duplicate title or id
$exists_title = sqlQuery("SELECT * FROM list_options WHERE ".
                    " list_id='".$list_id."'".
                    " and title='".trim($title). "'" 
                    );
if ($exists_title) { 
	echo json_encode(array("error"=> xl('Record already exist') ));
	exit; 
}

$exists_id = sqlQuery("SELECT * FROM list_options WHERE ".
                    " list_id='".$list_id."'".
                    " and option_id='".trim($option_id)."'"
                    );
if ($exists_id) { 
	echo json_encode(array("error"=> xl('Record already exist') ));
	exit; 
}

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
echo '{ "error":"", "options": [';
// send the 'Unassigned' empty variable
echo '{"id":"","title":"' . xl('Unassigned') . '"}';
$comma = ",";
$lres = sqlStatement("SELECT * FROM list_options WHERE list_id = '$list_id' ORDER BY seq");
while ($lrow = sqlFetchArray($lres)) {
    echo $comma;
    echo '{"id":"'.$lrow['option_id'].'",';
    
    // translate title if translate-lists flag set and not english
    if ($GLOBALS['translate_lists'] && $_SESSION['language_choice'] > 1) {
     echo '"title":"' . xl($lrow['title']) .'"}';
    }
    else {
     echo '"title":"'.$lrow['title'].'"}';	
    }
}
echo "]}";
exit;

?>
