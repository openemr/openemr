<?php
// Copyright (C) 2010 Brady Miller <brady@sparmy.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This file contains functions that manage custom user
// settings
//

//SANITIZE ALL ESCAPES
$sanitize_all_escapes=true;
//

//STOP FAKE REGISTER GLOBALS
$fake_register_globals=false;
//

require_once(dirname(__FILE__) . "/../../interface/globals.php");
require_once(dirname(__FILE__) . "/../user.inc");

//If 'mode' is either a 1 or 0 and 'target' is set
//  Then will update the appropriate user flag
if (($_POST['mode'] == 1 || $_POST['mode'] == 0) && isset($_POST['target'])) {

  //Set the demographics expand setting in the patient summary screen
  if ($_POST['target'] == "#DEM") {
    setUserSetting("dem_expand", $_POST['mode'], $_SESSION['authUserID']);
  }

  //Set the insurance expand setting in the patient summary screen
  if ($_POST['target'] == "#INSURANCE") {
    setUserSetting("ins_expand", $_POST['mode'], $_SESSION['authUserID']);
  }

  //Set the patient notes expand setting in the patient summary screen
  if ($_POST['target'] == "#notes_div") {
    setUserSetting("not_expand", $_POST['mode'], $_SESSION['authUserID']);
  }

  //Set the disclosures expand setting in the patient summary screen
  if ($_POST['target'] == "#disc_div") {
    setUserSetting("dis_expand", $_POST['mode'], $_SESSION['authUserID']);
  }
}
?>
