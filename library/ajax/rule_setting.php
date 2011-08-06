<?php
// Copyright (C) 2011 Brady Miller <brady@sparmy.com>
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
require_once(dirname(__FILE__) . "/../clinical_rules.php");

//set the rule setting for patient (ensure all variables exist)
if ($_POST['rule'] && $_POST['type'] && $_POST['setting'] && $_POST['patient_id']) {
  set_rule_activity_patient($_POST['rule'], $_POST['type'], $_POST['setting'], $_POST['patient_id']);
}

?>
