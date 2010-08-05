<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/sql.inc");
require_once(dirname(__FILE__) . "/formdata.inc.php");

//This will return the user settings from the 'users' table.
// $id is the user id number in the 'users' table
function getUserSettings($id) {
  $row = sqlQuery("SELECT dem_expand, ins_expand, not_expand, " .
    "dis_expand FROM users WHERE id=?", array($id) );
  return $row;
}

//This will set a user setting
// $name is the setting name in the 'users' table
// $value is the setting value to be set in the 'users' table
// $id is the user id number in the 'users' table
function setUserSetting($name, $value, $id) {
  sqlStatement("UPDATE users SET " . add_escape_custom($name) .
    "=? WHERE id=?", array($value, $id) );
}

?>