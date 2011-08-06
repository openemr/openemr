<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This file contains functions to manage some AMC items.
//

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../amc.php");

//  If all items are valid(ie. not empty) (note object_category and object_id and date_created can be empty), then proceed.
if ( !(empty($_POST['amc_id'])) && 
     !(empty($_POST['complete'])) &&
     !(empty($_POST['mode'])) &&
     !(empty($_POST['patient_id'])) ) {

  processAmcCall($_POST['amc_id'], $_POST['complete'], $_POST['mode'], $_POST['patient_id'], $_POST['object_category'], $_POST['object_id'], $_POST['date_created']);

}
?>
