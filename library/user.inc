<?php
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once(dirname(__FILE__) . "/sql.inc");
require_once(dirname(__FILE__) . "/formdata.inc.php");

//This will return the user setting(s) from the 'users' table.
// $choice can be used to return only one specific user setting
// $user is the user id number in the 'users' table
function getUserSetting($label,$user=NULL) {

  // if no user id is sent, then use the currently logged in user
  if (!isset($user)) {
    $user = $_SESSION['authUserID'];
  }
    
  // Collect entry if it exist.
  //  If does not exist, then look for a default entry.
  //    If default entry does not exist, then return a '0'.
  // setting.
  $result = sqlQuery("SELECT setting_value FROM user_settings " .
    "WHERE setting_user=? AND setting_label=?", array($user, $label) );
  if (empty($result)) {
    $result = sqlQuery("SELECT setting_value FROM user_settings " .
      "WHERE setting_user='0' AND setting_label=?", array($label) );
    if (empty($result)) {
      return '0';
    }
  }
  
  // Only one value, so collect it
  $tempResult = $result;
  foreach($tempResult as $key=>$value) {
    $result = $value;
  }
  
  return $result;
}

//This will set a user setting
// $name is the setting name in the 'users' table
// $value is the setting value to be set in the 'users' table
// $user is the user id number in the 'users' table
function setUserSetting($label, $value, $user=NULL) {

  // if no user id is sent, then use the currently logged in user
  if (!isset($user)) {
    $user = $_SESSION['authUserID'];
  }

  $newCustom=false;
  $newDefault=false;

  // Check for a custom settings
  $row = sqlQuery("SELECT setting_value FROM user_settings " .
    "WHERE setting_user=? AND setting_label=?", array($user, $label) );
  if (empty($row)) {
    $newCustom=true;
  }

  // Set the custom setting
  if ($newCustom) {
    sqlStatement("INSERT INTO user_settings " .
      "(setting_user, setting_label, setting_value) " .
      "VALUES (?,?,?)", array($user, $label, $value) );
  }
  else {
    sqlStatement("UPDATE user_settings SET setting_value=? " .
      "WHERE setting_user=? AND setting_label=?", array($value, $user, $label) );
  }

  // Check for a default settings
  $row = sqlQuery("SELECT setting_value FROM user_settings " .
    "WHERE setting_user='0' AND setting_label=?", array($label) );
  if (empty($row)) {
    $newDefault=true;
  }

  // Place a default setting if it does not already exist
  if ($newDefault) {
    sqlStatement("INSERT INTO user_settings " .
      "(setting_user, setting_label, setting_value) " .
      "VALUES ('0',?,?)", array($label, $value) );
  }
}

?>
